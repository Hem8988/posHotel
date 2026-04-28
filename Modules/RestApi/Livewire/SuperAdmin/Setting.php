<?php
namespace Modules\RestApi\Livewire\SuperAdmin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Livewire\Component;
use Modules\RestApi\Entities\ApplicationIntegrationSetting;
use Modules\RestApi\Support\Safety\SafetyGuard;

class Setting extends Component
{
    public string $email = '';
    public string $password = '';
    public string $locale;
    public array $results = [];
    public ?string $publicLink = null;
    public ?string $publicToken = null;
    public string $docUrl;
    public bool $isBusy = false;

    protected array $rules = [
        'email' => 'required|string',
        'password' => 'required|string',
    ];

    public function mount(): void
    {
        $this->locale = app()->getLocale();
        $this->docUrl = route('applicationintegration.docs', ['lang' => $this->locale]);
        $settings = ApplicationIntegrationSetting::instance();
        $this->publicToken = $settings->public_token;
        $this->publicLink = $this->publicToken
            ? route('applicationintegration.docs.public', ['token' => $this->publicToken, 'lang' => $this->locale])
            : null;
    }

    public function generatePublicLink(): void
    {
        $settings = ApplicationIntegrationSetting::instance();
        $settings->public_token = Str::random(48);
        $settings->generated_by = Auth::id();
        $settings->generated_at = now();
        $settings->save();

        $this->publicToken = $settings->public_token;
        $this->publicLink = route('applicationintegration.docs.public', [
            'token' => $settings->public_token,
            'lang' => $this->locale,
        ]);

        $this->dispatch('ai-doc-link', value: $this->publicLink);
        $this->dispatch('notify', type: 'success', message: __('applicationintegration::messages.public_link_created'));
    }

    public function revokePublicLink(): void
    {
        $settings = ApplicationIntegrationSetting::instance();
        $settings->public_token = null;
        $settings->generated_by = null;
        $settings->generated_at = null;
        $settings->save();

        $this->publicToken = null;
        $this->publicLink = null;

        $this->dispatch('notify', type: 'success', message: __('applicationintegration::messages.public_link_revoked'));
    }

    public function copyDocs(): void
    {
        $this->dispatch('ai-doc-link', value: $this->docUrl);
    }

    public function copyPublic(): void
    {
        if ($this->publicLink) {
            $this->dispatch('ai-doc-link', value: $this->publicLink);
        }
    }

    public function runDiagnostics(): void
    {
        $this->validate();
        $this->isBusy = true;
        $this->results = [];

        $base = url('/api/application-integration');
        $context = [
            'branch_id' => null,
            'restaurant_id' => null,
            'menu_id' => null,
            'category_id' => null,
            'item_id' => null,
            'order_type' => null,
            'table_id' => null,
            'order_id' => null,
            'reservation_id' => null,
            'notification_id' => null,
            'customer_id' => null,
            'customer_address_id' => null,
        ];

        $login = Http::acceptJson()->post($base . '/auth/login', [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        if ($login->failed()) {
            $this->results[] = [
                'label' => 'auth/login',
                'status' => 'fail',
                'message' => $login->json('message') ?? $login->body(),
                'http_status' => $login->status(),
            ];
            $this->isBusy = false;
            return;
        }

        $token = $login->json('data.access_token');
        $context['branch_id'] = $login->json('data.user.branch_id');
        $context['restaurant_id'] = $login->json('data.user.restaurant_id');
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];

        $this->results[] = [
            'label' => 'auth/login',
            'status' => 'ok',
            'message' => __('applicationintegration::messages.token_loaded'),
            'http_status' => $login->status(),
        ];

        $httpFactory = fn() => Http::withHeaders($headers);

        $run = function (array $endpoint) use (&$context, $base, $httpFactory) {
            if (! empty($endpoint['requires'])) {
                $missing = collect($endpoint['requires'])->filter(fn($key) => empty($context[$key]));
                if ($missing->isNotEmpty()) {
                    $this->results[] = [
                        'label' => $endpoint['label'],
                        'status' => 'ok',
                        'message' => $endpoint['skip_message'] ?? __('applicationintegration::messages.skipped_missing_context', ['fields' => $missing->implode(', ')]),
                        'http_status' => null,
                    ];
                    return;
                }
            }

            $path = $this->resolvePath($endpoint['path'], $context);
            $payload = $endpoint['payload'] ?? [];

            if (isset($endpoint['payload_resolver']) && is_callable($endpoint['payload_resolver'])) {
                $payload = $endpoint['payload_resolver']($context);
            }

            try {
                $method = strtoupper($endpoint['method'] ?? 'GET');
                $client = $httpFactory();
                switch ($method) {
                    case 'POST':
                        $response = $client->post($base . $path, $payload);
                        break;
                    case 'PUT':
                        $response = $client->put($base . $path, $payload);
                        break;
                    case 'PATCH':
                        $response = $client->patch($base . $path, $payload);
                        break;
                    case 'DELETE':
                        $response = $client->delete($base . $path, $payload);
                        break;
                    default:
                        $response = $client->get($base . $path);
                        break;
                }

                [$status, $message] = $this->normalizeResponse($response, $endpoint);

                if (isset($endpoint['capture']) && is_callable($endpoint['capture']) && $response->successful()) {
                    ($endpoint['capture'])($response, $context);
                }

                $this->results[] = [
                    'label' => $endpoint['label'],
                    'status' => $status,
                    'message' => $message,
                    'http_status' => $response->status(),
                ];
            } catch (\Throwable $e) {
                $this->results[] = [
                    'label' => $endpoint['label'],
                    'status' => 'fail',
                    'message' => $e->getMessage(),
                    'http_status' => null,
                ];
            }
        };

        $endpoints = [
            ['label' => 'auth/me', 'method' => 'GET', 'path' => '/auth/me', 'capture' => function ($response, &$ctx) {
                $ctx['branch_id'] = $ctx['branch_id'] ?? data_get($response->json(), 'user.branch_id');
                $ctx['restaurant_id'] = $ctx['restaurant_id'] ?? data_get($response->json(), 'user.restaurant_id');
            }],
            ['label' => 'platform/config', 'method' => 'GET', 'path' => '/platform/config', 'capture' => function ($response, &$ctx) {
                $ctx['branch_id'] = $ctx['branch_id'] ?? data_get($response->json(), 'branch.id');
                $ctx['restaurant_id'] = $ctx['restaurant_id'] ?? data_get($response->json(), 'restaurant.id');
            }],
            ['label' => 'platform/permissions', 'method' => 'GET', 'path' => '/platform/permissions'],
            ['label' => 'platform/printers', 'method' => 'GET', 'path' => '/platform/printers'],
            ['label' => 'platform/receipt-settings', 'method' => 'GET', 'path' => '/platform/receipt-settings'],
            ['label' => 'pos/restaurants', 'method' => 'GET', 'path' => '/pos/restaurants', 'capture' => function ($response, &$ctx) {
                $first = collect($response->json())->first();
                $ctx['restaurant_id'] = $ctx['restaurant_id'] ?? ($first['id'] ?? null);
            }],
            ['label' => 'pos/branches', 'method' => 'GET', 'path' => '/pos/branches', 'capture' => function ($response, &$ctx) {
                $branch = collect($response->json())->first();
                $ctx['branch_id'] = $branch['id'] ?? ($ctx['branch_id'] ?? null);
            }],
            ['label' => 'platform/switch-branch', 'method' => 'POST', 'path' => '/platform/switch-branch', 'requires' => ['branch_id'], 'payload_resolver' => fn($ctx) => ['branch_id' => $ctx['branch_id']], 'allow_not_found' => true],
            ['label' => 'languages', 'method' => 'GET', 'path' => '/languages'],
            ['label' => 'currencies', 'method' => 'GET', 'path' => '/currencies'],
            ['label' => 'payment-gateways', 'method' => 'GET', 'path' => '/payment-gateways'],
            ['label' => 'staff', 'method' => 'GET', 'path' => '/staff'],
            ['label' => 'roles', 'method' => 'GET', 'path' => '/roles'],
            ['label' => 'areas', 'method' => 'GET', 'path' => '/areas'],
            ['label' => 'pos/order-types', 'method' => 'GET', 'path' => '/pos/order-types', 'capture' => function ($response, &$ctx) {
                $first = collect($response->json())->first();
                $ctx['order_type'] = $first['slug'] ?? $first['type'] ?? $ctx['order_type'];
            }],
            ['label' => 'pos/menus', 'method' => 'GET', 'path' => '/pos/menus', 'capture' => function ($response, &$ctx) {
                $menu = collect($response->json())->first();
                $ctx['menu_id'] = $menu['id'] ?? $ctx['menu_id'];
            }],
            ['label' => 'pos/categories', 'method' => 'GET', 'path' => '/pos/categories', 'capture' => function ($response, &$ctx) {
                $category = collect($response->json())->first();
                $ctx['category_id'] = $category['id'] ?? $ctx['category_id'];
            }],
            ['label' => 'pos/items', 'method' => 'GET', 'path' => '/pos/items', 'capture' => function ($response, &$ctx) {
                $item = collect($response->json())->first();
                $ctx['item_id'] = $item['id'] ?? $ctx['item_id'];
                $ctx['category_id'] = $ctx['category_id'] ?? ($item['category_id'] ?? null);
                $ctx['menu_id'] = $ctx['menu_id'] ?? ($item['menu_id'] ?? null);
            }],
            ['label' => 'pos/items/category', 'method' => 'GET', 'path' => '/pos/items/category/{category_id}', 'requires' => ['category_id'], 'skip_message' => __('applicationintegration::messages.no_categories')],
            ['label' => 'pos/items/menu', 'method' => 'GET', 'path' => '/pos/items/menu/{menu_id}', 'requires' => ['menu_id'], 'skip_message' => __('applicationintegration::messages.no_menus')],
            ['label' => 'pos/items/{id}', 'method' => 'GET', 'path' => '/pos/items/{item_id}', 'requires' => ['item_id'], 'skip_message' => __('applicationintegration::messages.no_items')],
            ['label' => 'pos/items/{id}/variations', 'method' => 'GET', 'path' => '/pos/items/{item_id}/variations', 'requires' => ['item_id'], 'skip_message' => __('applicationintegration::messages.no_items'), 'allow_not_found' => true],
            ['label' => 'pos/items/{id}/modifier-groups', 'method' => 'GET', 'path' => '/pos/items/{item_id}/modifier-groups', 'requires' => ['item_id'], 'skip_message' => __('applicationintegration::messages.no_items'), 'allow_not_found' => true],
            ['label' => 'pos/extra-charges', 'method' => 'GET', 'path' => '/pos/extra-charges/{order_type}', 'requires' => ['order_type'], 'skip_message' => __('applicationintegration::messages.no_order_type'), 'allow_not_found' => true],
            ['label' => 'pos/tables', 'method' => 'GET', 'path' => '/pos/tables', 'capture' => function ($response, &$ctx) {
                $table = collect($response->json('tables'))->first();
                $ctx['table_id'] = $table['id'] ?? $ctx['table_id'];
            }],
            ['label' => 'pos/tables/unlock', 'method' => 'POST', 'path' => '/pos/tables/{table_id}/unlock', 'requires' => ['table_id'], 'skip_message' => __('applicationintegration::messages.no_tables'), 'allow_not_found' => true],
            ['label' => 'pos/waiters', 'method' => 'GET', 'path' => '/pos/waiters'],
            ['label' => 'pos/delivery-platforms', 'method' => 'GET', 'path' => '/pos/delivery-platforms'],
            ['label' => 'pos/get-order-number', 'method' => 'GET', 'path' => '/pos/get-order-number'],
            ['label' => 'pos/delivery-executives', 'method' => 'GET', 'path' => '/pos/delivery-executives'],
            ['label' => 'pos/taxes', 'method' => 'GET', 'path' => '/pos/taxes'],
            ['label' => 'pos/phone-codes', 'method' => 'GET', 'path' => '/pos/phone-codes'],
            ['label' => 'pos/orders', 'method' => 'GET', 'path' => '/pos/orders', 'capture' => function ($response, &$ctx) {
                $order = collect($response->json('data'))->first();
                $ctx['order_id'] = $order['id'] ?? $ctx['order_id'];
            }],
            ['label' => 'pos/orders single', 'method' => 'GET', 'path' => '/pos/orders/{order_id}', 'requires' => ['order_id'], 'allow_not_found' => true, 'skip_message' => __('applicationintegration::messages.no_orders')],
            ['label' => 'pos/orders/status', 'method' => 'POST', 'path' => '/pos/orders/{order_id}/status', 'requires' => ['order_id'], 'payload_resolver' => fn($ctx) => ['status' => 'confirmed'], 'allow_not_found' => true, 'skip_message' => __('applicationintegration::messages.no_orders')],
            ['label' => 'pos/orders/pay', 'method' => 'POST', 'path' => '/pos/orders/{order_id}/pay', 'requires' => ['order_id'], 'payload' => ['payment_method' => 'cash'], 'allow_not_found' => true, 'skip_message' => __('applicationintegration::messages.no_orders')],
            ['label' => 'pos/orders (create)', 'method' => 'POST', 'path' => '/pos/orders', 'payload_resolver' => function ($ctx) {
                $payload = ['order_type' => $ctx['order_type'] ?? 'dinein', 'items' => []];
                if (! empty($ctx['item_id'])) {
                    $payload['items'][] = ['id' => $ctx['item_id'], 'quantity' => 1];
                }
                if (! empty($ctx['table_id'])) {
                    $payload['table_id'] = $ctx['table_id'];
                }
                return $payload;
            }],
            ['label' => 'pos/reservations', 'method' => 'GET', 'path' => '/pos/reservations', 'capture' => function ($response, &$ctx) {
                $res = collect($response->json('data'))->first();
                $ctx['reservation_id'] = $res['id'] ?? $ctx['reservation_id'];
            }],
            ['label' => 'pos/reservations (create)', 'method' => 'POST', 'path' => '/pos/reservations', 'requires' => ['table_id'], 'skip_message' => __('applicationintegration::messages.no_tables'), 'payload_resolver' => fn($ctx) => ['table_id' => $ctx['table_id'], 'reservation_date_time' => now()->addHour()->toDateTimeString(), 'party_size' => 2, 'name' => 'API Tester', 'phone' => '999000123'], 'allow_not_found' => true],
            ['label' => 'pos/reservations/today', 'method' => 'GET', 'path' => '/pos/reservations/today'],
            ['label' => 'pos/reservations/status', 'method' => 'POST', 'path' => '/pos/reservations/{reservation_id}/status', 'requires' => ['reservation_id'], 'payload' => ['status' => 'confirmed'], 'allow_not_found' => true, 'skip_message' => __('applicationintegration::messages.no_reservations')],
            ['label' => 'pos/actions', 'method' => 'GET', 'path' => '/pos/actions'],
            ['label' => 'customer/catalog', 'method' => 'GET', 'path' => '/customer/catalog'],
            ['label' => 'customer/orders', 'method' => 'GET', 'path' => '/customer/orders'],
            ['label' => 'customer/orders (filtered)', 'method' => 'GET', 'path' => '/customer/orders?status=open'],
            ['label' => 'pos/customers', 'method' => 'GET', 'path' => '/pos/customers', 'capture' => function ($response, &$ctx) {
                $customer = collect($response->json())->first();
                $ctx['customer_id'] = $customer['id'] ?? $ctx['customer_id'];
            }],
            ['label' => 'pos/customers (create)', 'method' => 'POST', 'path' => '/pos/customers', 'payload_resolver' => function () {
                return ['name' => 'Diagnostic Customer', 'phone_code' => '+1', 'phone' => '999' . rand(1000, 9999), 'email' => null];
            }, 'capture' => function ($response, &$ctx) {
                $ctx['customer_id'] = data_get($response->json(), 'customer.id') ?? $ctx['customer_id'];
            }],
            ['label' => 'customer-addresses', 'method' => 'GET', 'path' => '/customer-addresses?customer_id={customer_id}', 'requires' => ['customer_id'], 'skip_message' => __('applicationintegration::messages.no_customers'), 'capture' => function ($response, &$ctx) {
                $address = collect($response->json())->first();
                $ctx['customer_address_id'] = $address['id'] ?? $ctx['customer_address_id'];
            }],
            ['label' => 'customer-addresses (create)', 'method' => 'POST', 'path' => '/customer-addresses', 'requires' => ['customer_id'], 'skip_message' => __('applicationintegration::messages.no_customers'), 'payload_resolver' => fn($ctx) => ['customer_id' => $ctx['customer_id'], 'address' => 'diagnostic']],
            ['label' => 'customer-addresses (update)', 'method' => 'PUT', 'path' => '/customer-addresses/{customer_address_id}', 'requires' => ['customer_id', 'customer_address_id'], 'skip_message' => __('applicationintegration::messages.no_customers'), 'payload_resolver' => fn($ctx) => ['customer_id' => $ctx['customer_id'], 'address' => 'diagnostic-updated'], 'allow_not_found' => true],
            ['label' => 'customer-addresses (delete)', 'method' => 'DELETE', 'path' => '/customer-addresses/{customer_address_id}', 'requires' => ['customer_address_id'], 'skip_message' => __('applicationintegration::messages.no_customers'), 'allow_not_found' => true],
            ['label' => 'notifications/register-token', 'method' => 'POST', 'path' => '/pos/notifications/register-token', 'payload' => ['token' => 'diag-token-' . uniqid('', false), 'platform' => 'web']],
            ['label' => 'notifications', 'method' => 'GET', 'path' => '/pos/notifications', 'capture' => function ($response, &$ctx) {
                $notification = collect($response->json('data'))->first();
                $ctx['notification_id'] = $notification['id'] ?? $ctx['notification_id'];
            }],
            ['label' => 'notifications/read', 'method' => 'POST', 'path' => '/pos/notifications/{notification_id}/read', 'requires' => ['notification_id'], 'skip_message' => __('applicationintegration::messages.no_notifications'), 'allow_not_found' => true, 'not_found_message' => __('applicationintegration::messages.no_notifications')],
            ['label' => 'notifications/test', 'method' => 'POST', 'path' => '/pos/notifications/test', 'payload' => ['title' => 'Diagnostic', 'body' => 'Testing notifications', 'data' => []]],
            ['label' => 'payment-gateways (cache)', 'method' => 'GET', 'path' => '/payment-gateways?refresh=1'],
            ['label' => 'languages (fallback)', 'method' => 'GET', 'path' => '/languages?include_inactive=1'],
        ];

        foreach ($endpoints as $endpoint) {
            $run($endpoint);
        }

        $guard = new SafetyGuard($context['branch_id'], $context['restaurant_id']);
        $issues = $guard->audit();
        if (empty($issues)) {
            $this->results[] = [
                'label' => 'safety/audit',
                'status' => 'ok',
                'message' => __('applicationintegration::messages.safety_ok'),
                'http_status' => null,
            ];
        } else {
            foreach ($issues as $issue) {
                $this->results[] = [
                    'label' => 'safety/audit ' . ($issue['type'] ?? 'generic'),
                    'status' => 'warn',
                    'message' => ($issue['message'] ?? 'Issue detected') . ' (' . ($issue['count'] ?? 0) . ')',
                    'http_status' => null,
                ];
            }
        }

        $this->isBusy = false;
        $this->dispatch('ai-test-finished');
    }

    protected function normalizeResponse($response, array $endpoint = []): array
    {
        $status = 'fail';
        $bodyStatus = $response->json('status');
        $message = $response->json('message') ?? ('HTTP ' . $response->status());

        if ($response->successful() || $response->status() === 422) {
            $status = $bodyStatus === false ? 'fail' : 'ok';
            $message = $message ?: __('applicationintegration::messages.status_ok');
        } elseif ($response->status() === 404 && ($endpoint['allow_not_found'] ?? false)) {
            $status = 'ok';
            $message = $endpoint['not_found_message'] ?? $message;
        } elseif ($response->status() === 403) {
            $status = 'permission';
            $message = $message ?: __('applicationintegration::messages.status_permission');
        } elseif ($response->status() === 401) {
            $status = 'fail';
            $message = __('applicationintegration::messages.unauthorized');
        }

        return [$status, $message];
    }

    protected function resolvePath(string $path, array $context): string
    {
        return preg_replace_callback('/{([^}]+)}/', function ($matches) use ($context) {
            $key = $matches[1];
            return $context[$key] ?? $matches[0];
        }, $path);
    }

    public function render()
    {
        return view('applicationintegration::super-admin.setting');
    }
}
