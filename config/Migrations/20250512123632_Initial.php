<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class Initial extends BaseMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     * @return void
     */
    public function up(): void
    {
        $this->table('appointments', ['id' => false, 'primary_key' => ['appointment_id']])
            ->addColumn('appointment_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('user_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('appointment_type', 'string', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('appointment_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('appointment_time', 'time', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('duration', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('google_calendar_event_id', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('is_deleted', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('updated_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('user_id')
                    ->setName('idx_appointments_user')
            )
            ->addIndex(
                $this->index('google_calendar_event_id')
                    ->setName('idx_appointments_event')
            )
            ->create();

        $this->table('artwork_variant_carts', ['id' => false, 'primary_key' => ['artwork_variant_cart_id']])
            ->addColumn('artwork_variant_cart_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('artwork_variant_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('cart_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('quantity', 'integer', [
                'default' => '1',
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('date_added', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('is_deleted', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index([
                        'cart_id',
                        'artwork_variant_id',
                    ])
                    ->setName('ux_cart_variant')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('cart_id')
                    ->setName('idx_artwork_variant_carts_cart')
            )
            ->addIndex(
                $this->index('artwork_variant_id')
                    ->setName('idx_artwork_variant_carts_variant')
            )
            ->create();

        $this->table('artwork_variant_orders', ['id' => false, 'primary_key' => ['artwork_variant_order_id']])
            ->addColumn('artwork_variant_order_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('artwork_variant_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('order_id', 'char', [
                'default' => null,
                'limit' => 9,
                'null' => false,
            ])
            ->addColumn('quantity', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('price', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 10,
                'scale' => 2,
                'signed' => true,
            ])
            ->addColumn('subtotal', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 10,
                'scale' => 2,
                'signed' => true,
            ])
            ->addColumn('is_deleted', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('updated_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('artwork_variant_id')
                    ->setName('idx_av_orders_variant')
            )
            ->addIndex(
                $this->index('order_id')
                    ->setName('idx_av_orders_order')
            )
            ->create();

        $this->table('artwork_variants', ['id' => false, 'primary_key' => ['artwork_variant_id']])
            ->addColumn('artwork_variant_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('artwork_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('dimension', 'string', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('price', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 10,
                'scale' => 2,
                'signed' => true,
            ])
            ->addColumn('is_deleted', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('artwork_id')
                    ->setName('idx_av_artwork')
            )
            ->create();

        $this->table('artworks', ['id' => false, 'primary_key' => ['artwork_id']])
            ->addColumn('artwork_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('availability_status', 'string', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('max_copies', 'integer', [
                'default' => '5',
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('is_deleted', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('updated_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('carts', ['id' => false, 'primary_key' => ['cart_id']])
            ->addColumn('cart_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('user_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('session_id', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('updated_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('user_id')
                    ->setName('idx_carts_user')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('session_id')
                    ->setName('idx_carts_session')
                    ->setType('unique')
            )
            ->create();

        $this->table('content_blocks', ['id' => false, 'primary_key' => ['content_block_id']])
            ->addColumn('content_block_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('parent', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('slug', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('label', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('type', 'string', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('value', 'text', [
                'default' => null,
                'limit' => 4294967295,
                'null' => true,
            ])
            ->addColumn('previous_value', 'text', [
                'default' => null,
                'limit' => 4294967295,
                'null' => true,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('updated_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('slug')
                    ->setName('idx_content_blocks_slug')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('parent')
                    ->setName('idx_content_blocks_parent')
            )
            ->create();

        $this->table('notifications', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'comment' => 'UUID primary key',
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('type', 'string', [
                'comment' => 'Notification type',
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('entity_id', 'uuid', [
                'comment' => 'ID of the related entity (order, request or message)',
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('actor_user_id', 'uuid', [
                'comment' => 'ID of the user who triggered the notification',
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('target_role', 'string', [
                'comment' => 'Role of the recipient',
                'default' => 'admin',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('message', 'text', [
                'comment' => 'Notification text to display',
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('is_read', 'boolean', [
                'comment' => 'Read flag (0 = unread, 1 = read)',
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created_at', 'datetime', [
                'comment' => 'Timestamp when notification was created',
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index([
                        'is_read',
                        'target_role',
                    ])
                    ->setName('idx_notifications_unread')
            )
            ->addIndex(
                $this->index([
                        'type',
                        'entity_id',
                    ])
                    ->setName('idx_notifications_entity')
            )
            ->addIndex(
                $this->index('actor_user_id')
                    ->setName('idx_notifications_actor')
            )
            ->create();

        $this->table('orders', ['id' => false, 'primary_key' => ['order_id']])
            ->addColumn('order_id', 'char', [
                'default' => null,
                'limit' => 9,
                'null' => false,
            ])
            ->addColumn('user_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('total_amount', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 10,
                'scale' => 2,
                'signed' => true,
            ])
            ->addColumn('order_status', 'string', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('order_date', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('billing_first_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('billing_last_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('billing_company', 'string', [
                'default' => '',
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('billing_email', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('shipping_country', 'char', [
                'default' => null,
                'limit' => 2,
                'null' => false,
            ])
            ->addColumn('shipping_address1', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('shipping_address2', 'string', [
                'default' => '',
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('shipping_suburb', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('shipping_state', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('shipping_postcode', 'string', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('shipping_phone', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('order_notes', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('is_deleted', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('updated_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('user_id')
                    ->setName('idx_orders_user')
            )
            ->addIndex(
                $this->index([
                        'order_status',
                        'order_date',
                    ])
                    ->setName('idx_orders_status_date')
            )
            ->create();

        $this->table('payments', ['id' => false, 'primary_key' => ['payment_id']])
            ->addColumn('payment_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('order_id', 'char', [
                'default' => null,
                'limit' => 9,
                'null' => false,
            ])
            ->addColumn('amount', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 10,
                'scale' => 2,
                'signed' => true,
            ])
            ->addColumn('transaction_id', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('payment_date', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('payment_method', 'string', [
                'default' => 'stripe',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('status', 'string', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('is_deleted', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('order_id')
                    ->setName('idx_payments_order')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('transaction_id')
                    ->setName('transaction_id')
                    ->setType('unique')
            )
            ->create();

        $this->table('request_messages', ['id' => false, 'primary_key' => ['request_message_id']])
            ->addColumn('request_message_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('writing_service_request_id', 'char', [
                'default' => null,
                'limit' => 9,
                'null' => false,
            ])
            ->addColumn('user_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('message', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('message_type', 'string', [
                'default' => 'text',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('is_deleted', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('updated_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('is_read', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('writing_service_request_id')
                    ->setName('idx_rm_request')
            )
            ->addIndex(
                $this->index('user_id')
                    ->setName('idx_rm_user')
            )
            ->create();

        $this->table('trusted_devices', ['id' => false, 'primary_key' => ['trusted_device_id']])
            ->addColumn('trusted_device_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('user_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('device_id', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('expires', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index([
                        'user_id',
                        'device_id',
                    ])
                    ->setName('idx_td_user_device')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('user_id')
                    ->setName('idx_td_user')
            )
            ->create();

        $this->table('two_factor_codes', ['id' => false, 'primary_key' => ['two_factor_code_id']])
            ->addColumn('two_factor_code_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('user_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('code', 'char', [
                'default' => null,
                'limit' => 6,
                'null' => false,
            ])
            ->addColumn('expires', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('user_id')
                    ->setName('idx_tfc_user')
            )
            ->addIndex(
                $this->index([
                        'user_id',
                        'code',
                        'expires',
                    ])
                    ->setName('idx_tfc_user_code')
            )
            ->create();

        $this->table('users', ['id' => false, 'primary_key' => ['user_id']])
            ->addColumn('user_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('first_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('last_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('email', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('password', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('phone_number', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('street_address', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('street_address2', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('suburb', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('state', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('postcode', 'string', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('country', 'char', [
                'default' => null,
                'limit' => 2,
                'null' => false,
            ])
            ->addColumn('user_type', 'string', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('password_reset_token', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('is_verified', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('token_expiration', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('last_login', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('is_deleted', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('updated_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('email')
                    ->setName('email')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('user_type')
                    ->setName('idx_users_type')
            )
            ->create();

        $this->table('writing_service_payments', ['id' => false, 'primary_key' => ['writing_service_payment_id']])
            ->addColumn('writing_service_payment_id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('writing_service_request_id', 'string', [
                'default' => null,
                'limit' => 9,
                'null' => false,
            ])
            ->addColumn('amount', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 10,
                'scale' => 2,
                'signed' => true,
            ])
            ->addColumn('transaction_id', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('payment_date', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('payment_method', 'string', [
                'default' => 'stripe',
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('status', 'string', [
                'default' => 'pending',
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('is_deleted', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('updated_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('writing_service_request_id')
                    ->setName('idx_wsp_request')
            )
            ->addIndex(
                $this->index('transaction_id')
                    ->setName('idx_wsp_transaction')
            )
            ->create();

        $this->table('writing_service_requests', ['id' => false, 'primary_key' => ['writing_service_request_id']])
            ->addColumn('writing_service_request_id', 'char', [
                'default' => null,
                'limit' => 9,
                'null' => false,
            ])
            ->addColumn('user_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('appointment_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('service_title', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('service_type', 'string', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('notes', 'string', [
                'default' => null,
                'limit' => 1000,
                'null' => true,
            ])
            ->addColumn('final_price', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 10,
                'scale' => 2,
                'signed' => true,
            ])
            ->addColumn('request_status', 'string', [
                'default' => 'pending',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('document', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('is_deleted', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('updated_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('user_id')
                    ->setName('idx_wsr_user')
            )
            ->addIndex(
                $this->index('request_status')
                    ->setName('idx_wsr_status')
            )
            ->create();

        $this->table('appointments')
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('user_id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_appointments_user')
            )
            ->update();

        $this->table('artwork_variant_carts')
            ->addForeignKey(
                $this->foreignKey('cart_id')
                    ->setReferencedTable('carts')
                    ->setReferencedColumns('cart_id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_av_carts_cart')
            )
            ->addForeignKey(
                $this->foreignKey('artwork_variant_id')
                    ->setReferencedTable('artwork_variants')
                    ->setReferencedColumns('artwork_variant_id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_av_carts_variant')
            )
            ->update();

        $this->table('artwork_variant_orders')
            ->addForeignKey(
                $this->foreignKey('order_id')
                    ->setReferencedTable('orders')
                    ->setReferencedColumns('order_id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_av_orders_order')
            )
            ->addForeignKey(
                $this->foreignKey('artwork_variant_id')
                    ->setReferencedTable('artwork_variants')
                    ->setReferencedColumns('artwork_variant_id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_av_orders_variant')
            )
            ->update();

        $this->table('artwork_variants')
            ->addForeignKey(
                $this->foreignKey('artwork_id')
                    ->setReferencedTable('artworks')
                    ->setReferencedColumns('artwork_id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_av_artwork')
            )
            ->update();

        $this->table('carts')
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('user_id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_carts_user')
            )
            ->update();

        $this->table('orders')
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('user_id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_orders_user')
            )
            ->update();

        $this->table('payments')
            ->addForeignKey(
                $this->foreignKey('order_id')
                    ->setReferencedTable('orders')
                    ->setReferencedColumns('order_id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_payments_order')
            )
            ->update();

        $this->table('request_messages')
            ->addForeignKey(
                $this->foreignKey('writing_service_request_id')
                    ->setReferencedTable('writing_service_requests')
                    ->setReferencedColumns('writing_service_request_id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_request_messages_request')
            )
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('user_id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_request_messages_user')
            )
            ->update();

        $this->table('trusted_devices')
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('user_id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_td_user')
            )
            ->update();

        $this->table('two_factor_codes')
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('user_id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_tfc_user')
            )
            ->update();

        $this->table('writing_service_payments')
            ->addForeignKey(
                $this->foreignKey('writing_service_request_id')
                    ->setReferencedTable('writing_service_requests')
                    ->setReferencedColumns('writing_service_request_id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('writing_service_payments_ibfk_1')
            )
            ->update();

        $this->table('writing_service_requests')
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('user_id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_wsr_user')
            )
            ->update();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     * @return void
     */
    public function down(): void
    {
        $this->table('appointments')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('artwork_variant_carts')
            ->dropForeignKey(
                'cart_id'
            )
            ->dropForeignKey(
                'artwork_variant_id'
            )->save();

        $this->table('artwork_variant_orders')
            ->dropForeignKey(
                'order_id'
            )
            ->dropForeignKey(
                'artwork_variant_id'
            )->save();

        $this->table('artwork_variants')
            ->dropForeignKey(
                'artwork_id'
            )->save();

        $this->table('carts')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('orders')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('payments')
            ->dropForeignKey(
                'order_id'
            )->save();

        $this->table('request_messages')
            ->dropForeignKey(
                'writing_service_request_id'
            )
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('trusted_devices')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('two_factor_codes')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('writing_service_payments')
            ->dropForeignKey(
                'writing_service_request_id'
            )->save();

        $this->table('writing_service_requests')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('appointments')->drop()->save();
        $this->table('artwork_variant_carts')->drop()->save();
        $this->table('artwork_variant_orders')->drop()->save();
        $this->table('artwork_variants')->drop()->save();
        $this->table('artworks')->drop()->save();
        $this->table('carts')->drop()->save();
        $this->table('content_blocks')->drop()->save();
        $this->table('notifications')->drop()->save();
        $this->table('orders')->drop()->save();
        $this->table('payments')->drop()->save();
        $this->table('request_messages')->drop()->save();
        $this->table('trusted_devices')->drop()->save();
        $this->table('two_factor_codes')->drop()->save();
        $this->table('users')->drop()->save();
        $this->table('writing_service_payments')->drop()->save();
        $this->table('writing_service_requests')->drop()->save();
    }
}
