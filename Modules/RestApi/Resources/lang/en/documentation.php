<?php

return [
    'title' => 'API Reference',
    'subtitle' => 'Developer Documentation',
    'intro' => 'Welcome to the complete API reference. This system allows deep integration with the POS backend, enabling you to build waiter apps, customer kiosks, or custom dashboards.',
    'search_placeholder' => 'Search endpoints...',
    'base_url' => 'Base URL',
    'auth_header' => 'Authentication',
    'auth_desc' => 'Authenticate via Bearer Token. Include `Authorization: Bearer <token>` in headers.',
    
    'sections' => [
        'auth' => 'Authentication',
        'platform' => 'Platform',
        'resources' => 'Resources',
        'customers' => 'Customers',
        'catalog' => 'Catalog',
        'sales' => 'Sales & Orders',
        'operations' => 'Operations',
        'hardware' => 'Hardware & Devices',
        'pusher' => 'Real-time & Push',
    ],

    'endpoints' => [
        'login' => ['title' => 'Login', 'desc' => 'Obtain access token.'],
        'me' => ['title' => 'User Profile', 'desc' => 'Get current user & permissions.'],
        
        'config' => ['title' => 'Config & Features', 'desc' => 'Get system settings, feature flags, and active modules.'],
        'permissions' => ['title' => 'Permissions', 'desc' => 'List user roles and capabilities.'],
        'printers' => ['title' => 'Printers', 'desc' => 'Get configured receipts/KOT printers.'],
        'receipts' => ['title' => 'Receipt Settings', 'desc' => 'Get receipt styling configuration.'],
        'switch_branch' => ['title' => 'Switch Branch', 'desc' => 'Change active branch context.'],

        'langs' => ['title' => 'Languages', 'desc' => 'Get available languages.'],
        'currencies' => ['title' => 'Currencies', 'desc' => 'Get system currencies.'],
        'gateways' => ['title' => 'Payment Gateways', 'desc' => 'Get public gateway credentials.'],
        'staff' => ['title' => 'Staff List', 'desc' => 'Get all staff members.'],
        'roles' => ['title' => 'Roles', 'desc' => 'Get available user roles.'],
        'areas' => ['title' => 'Areas', 'desc' => 'Get floor plan areas.'],

        'addr_list' => ['title' => 'List Addresses', 'desc' => 'Get addresses for a customer.'],
        'addr_create' => ['title' => 'Create Address', 'desc' => 'Add a new delivery address.'],
        'addr_update' => ['title' => 'Update Address', 'desc' => 'Modify an existing address.'],
        'addr_delete' => ['title' => 'Delete Address', 'desc' => 'Remove an address.'],

        'menus' => ['title' => 'Menus', 'desc' => 'Get active menus.'],
        'categories' => ['title' => 'Categories', 'desc' => 'Get item categories.'],
        'items' => ['title' => 'All Items', 'desc' => 'Get full item catalog with prices & modifiers.'],
        'items_filter' => ['title' => 'Filter Items', 'desc' => 'Get items by category or menu.'],
        'variations' => ['title' => 'Item Variations', 'desc' => 'Get variations for a specific item.'],
        'modifiers' => ['title' => 'Item Modifiers', 'desc' => 'Get modifier groups for a specific item.'],

        'orders_create' => ['title' => 'Submit Order', 'desc' => 'Create a new order (Dine-in/Delivery).'],
        'orders_list' => ['title' => 'List Orders', 'desc' => 'Get order history.'],
        'orders_detail' => ['title' => 'Order Detail', 'desc' => 'Get full order object.'],
        'orders_status' => ['title' => 'Update Status', 'desc' => 'Change order status (e.g. prepared).'],
        'orders_pay' => ['title' => 'Pay Order', 'desc' => 'Record payment and close order.'],
        'order_number' => ['title' => 'Preview Number', 'desc' => 'Get next order number.'],
        'order_types' => ['title' => 'Order Types', 'desc' => 'Get types (Dine-in, Takeaway).'],
        'actions' => ['title' => 'Allowed Actions', 'desc' => 'Get valid order actions (kot, bill).'],
        'platforms' => ['title' => 'Delivery Platforms', 'desc' => 'Get third-party platforms.'],
        'charges' => ['title' => 'Extra Charges', 'desc' => 'Get service charges/fees.'],
        'taxes' => ['title' => 'Taxes', 'desc' => 'Get configured tax rates.'],

        'tables' => ['title' => 'Tables', 'desc' => 'Get real-time table status.'],
        'unlock' => ['title' => 'Unlock Table', 'desc' => 'Force unlock a table.'],
        'res_today' => ['title' => 'Today\'s Reservations', 'desc' => 'Get reservations for the dashboard.'],
        'res_list' => ['title' => 'All Reservations', 'desc' => 'Get paginated reservations.'],
        'res_create' => ['title' => 'Create Reservation', 'desc' => 'Book a table.'],
        'res_status' => ['title' => 'Update Reservation', 'desc' => 'Change reservation status.'],

        'cust_search' => ['title' => 'Search Customers', 'desc' => 'Find by name/phone.'],
        'cust_save' => ['title' => 'Save Customer', 'desc' => 'Create or update profile.'],
        'waiters' => ['title' => 'Waiters', 'desc' => 'Get staff with waiter/driver roles.'],

        'multipos_reg' => ['title' => 'Register Device', 'desc' => 'Link physical hardware.'],
        'multipos_check' => ['title' => 'Check Device', 'desc' => 'Verify registration.'],
        'notif_token' => ['title' => 'Register FCM', 'desc' => 'Save push token.'],
        'notif_list' => ['title' => 'Notifications', 'desc' => 'Get in-app alerts.'],
        'notif_read' => ['title' => 'Mark Read', 'desc' => 'Dismiss notification.'],

        'pusher_settings' => ['title' => 'Get Pusher Settings', 'desc' => 'Retrieve complete Pusher configuration. Accessible to all authenticated users (superadmin, admin, staff).'],
        'pusher_broadcast' => ['title' => 'Get Broadcast Settings', 'desc' => 'Get Pusher real-time broadcast configuration. System-wide settings for all users.'],
        'pusher_beams' => ['title' => 'Get Beams Settings', 'desc' => 'Get Pusher Beams push notification configuration. Accessible to all authenticated users.'],
        'pusher_status' => ['title' => 'Check Pusher Status', 'desc' => 'Quick status check to verify if Pusher services are enabled. Available to all users.'],
        'pusher_authorize' => ['title' => 'Authorize Channel', 'desc' => 'Authorize user access to private and presence channels. Requires valid authentication.'],
        'pusher_presence' => ['title' => 'Get Presence Members', 'desc' => 'Retrieve list of users currently connected to a presence channel. System-wide data.'],
    ],
];

