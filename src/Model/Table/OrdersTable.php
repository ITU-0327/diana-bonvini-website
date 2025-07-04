<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Order;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Orders Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\ArtworkVariantOrdersTable&\Cake\ORM\Association\HasMany $ArtworkVariantOrders
 * @property \App\Model\Table\PaymentsTable&\Cake\ORM\Association\HasOne $Payments
 * @method \App\Model\Entity\Order newEmptyEntity()
 * @method \App\Model\Entity\Order newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Order> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Order get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Order findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Order patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Order> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Order|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Order saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Order>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Order>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Order>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Order> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Order>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Order>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Order>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Order> deleteManyOrFail(iterable $entities, array $options = [])
 */
class OrdersTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('orders');
        $this->setDisplayField('payment_method');
        $this->setPrimaryKey('order_id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);

        $this->hasOne('Payments', [
            'foreignKey' => 'order_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);

        $this->hasMany('ArtworkVariantOrders', [
            'foreignKey' => 'order_id',
            'dependent' => true,
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('user_id')
            ->notEmptyString('user_id');

        $validator
            ->decimal('total_amount')
            ->requirePresence('total_amount', 'create')
            ->notEmptyString('total_amount')
            ->add('total_amount', 'nonNegative', [
                'rule' => function ($value, $context) {
                    return $value >= 0;
                },
                'message' => 'Total amount must be non-negative.',
            ]);

        $validator
            ->decimal('shipping_cost')
            ->requirePresence('shipping_cost', 'create')
            ->notEmptyString('shipping_cost')
            ->add('shipping_cost', 'nonNegative', [
                'rule' => function ($value, $context) {
                    return $value >= 0;
                },
                'message' => 'Shipping cost must be non-negative.',
            ]);

        $validator
            ->scalar('order_status')
            ->requirePresence('order_status', 'create')
            ->notEmptyString('order_status');

        $validator
            ->dateTime('order_date')
            ->requirePresence('order_date', 'create')
            ->notEmptyDateTime('order_date');

        $validator
            ->scalar('billing_first_name')
            ->maxLength('billing_first_name', 255)
            ->requirePresence('billing_first_name', 'create')
            ->notEmptyString('billing_first_name')
            ->add('billing_first_name', 'alpha', [
                'rule' => ['custom', '/^[a-zA-Z \'-]+$/'],
                'message' => 'First name should only contain letters, spaces, apostrophes, and hyphens.',
            ]);

        $validator
            ->scalar('billing_last_name')
            ->maxLength('billing_last_name', 255)
            ->requirePresence('billing_last_name', 'create')
            ->notEmptyString('billing_last_name')
            ->add('billing_last_name', 'alpha', [
                'rule' => ['custom', '/^[a-zA-Z \'-]+$/'],
                'message' => 'Last name should only contain letters, spaces, apostrophes, and hyphens.',
            ]);

        $validator
            ->scalar('billing_company')
            ->maxLength('billing_company', 255)
            ->allowEmptyString('billing_company');

        $validator
            ->scalar('billing_email')
            ->maxLength('billing_email', 255)
            ->requirePresence('billing_email', 'create')
            ->notEmptyString('billing_email')
            ->email('billing_email', false, 'Please provide a valid email address.');

        $validator
            ->scalar('shipping_country')
            ->maxLength('shipping_country', 2)
            ->requirePresence('shipping_country', 'create')
            ->notEmptyString('shipping_country')
            ->inList('shipping_country', [
                'AU', 'US', 'CA', 'GB', 'NZ', 'JP', 'KR', 'SG', 'HK', 'CN', 'IN', 'DE', 'FR', 'IT', 'ES', 'NL', 'SE', 'NO', 'DK', 'FI', 'BR', 'MX', 'AR', 'CL', 'ZA', 'AE', 'IL', 'TR', 'RU', 'TH', 'VN', 'MY', 'ID', 'PH'
            ], 'Please select a valid country.');

        $validator
            ->scalar('shipping_address1')
            ->maxLength('shipping_address1', 255)
            ->requirePresence('shipping_address1', 'create')
            ->notEmptyString('shipping_address1');

        $validator
            ->scalar('shipping_address2')
            ->maxLength('shipping_address2', 255)
            ->allowEmptyString('shipping_address2');

        $validator
            ->scalar('shipping_suburb')
            ->maxLength('shipping_suburb', 255)
            ->requirePresence('shipping_suburb', 'create')
            ->notEmptyString('shipping_suburb');

        $validator
            ->scalar('shipping_state')
            ->maxLength('shipping_state', 50)
            ->requirePresence('shipping_state', 'create')
            ->notEmptyString('shipping_state');

        $validator
            ->scalar('shipping_postcode')
            ->maxLength('shipping_postcode', 20)
            ->requirePresence('shipping_postcode', 'create')
            ->notEmptyString('shipping_postcode')
            ->add('shipping_postcode', 'validFormat', [
                'rule' => function ($value, $context) {
                    // For international compatibility, allow various postal code formats
                    // Australian: 4 digits
                    // US/Canada: 5 digits or 5+4 format
                    // UK: Complex alphanumeric
                    // Other countries: alphanumeric up to 20 characters
                    $patterns = [
                        '/^[0-9]{4}$/',           // Australia (4 digits)
                        '/^[0-9]{5}(-[0-9]{4})?$/', // US (5 digits or 5+4)
                        '/^[A-Z][0-9][A-Z] [0-9][A-Z][0-9]$/', // Canada (A1A 1A1)
                        '/^[A-Z]{1,2}[0-9][A-Z0-9]? [0-9][A-Z]{2}$/i', // UK (SW1A 1AA)
                        '/^[0-9]{3}-[0-9]{4}$/',  // Japan (123-4567)
                        '/^[0-9]{5}$/',           // Many countries (5 digits)
                        '/^[0-9]{6}$/',           // Some countries (6 digits)
                        '/^[A-Z0-9]{2,20}$/i',    // General alphanumeric for other countries
                    ];
                    
                    foreach ($patterns as $pattern) {
                        if (preg_match($pattern, $value)) {
                            return true;
                        }
                    }
                    return false;
                },
                'message' => 'Please enter a valid postal code.',
            ]);

        $validator
            ->scalar('shipping_phone')
            ->maxLength('shipping_phone', 50)
            ->requirePresence('shipping_phone', 'create')
            ->notEmptyString('shipping_phone')
            ->add('shipping_phone', 'validFormat', [
                'rule' => ['custom', '/^[0-9\-\+\(\) ]+$/'],
                'message' => 'Please enter a valid phone number.',
            ]);

        $validator
            ->scalar('order_notes')
            ->allowEmptyString('order_notes');

        $validator
            ->boolean('is_deleted')
            ->notEmptyString('is_deleted');

        $validator
            ->dateTime('created_at')
            ->notEmptyDateTime('created_at');

        $validator
            ->dateTime('updated_at')
            ->notEmptyDateTime('updated_at');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    /**
     * @param \Cake\Event\EventInterface<\App\Model\Entity\Order> $event
     * @param \App\Model\Entity\Order $entity
     * @param \ArrayObject<string, mixed> $options
     * @return void
     * @throws \Random\RandomException
     */
    public function beforeSave(EventInterface $event, Order $entity, ArrayObject $options): void
    {
        if ($entity->isNew() && empty($entity->order_id)) {
            $entity->order_id = $this->_generateOrderId();
        }
    }

    /**
     * Override delete() to always soft-delete orders,
     * cascading to artwork_orders and payments.
     *
     * @param \Cake\Datasource\EntityInterface $entity The order entity.
     * @param array<string,mixed> $options Options passed from controller.
     * @return bool True on success, false on failure.
     */
    public function delete(EntityInterface $entity, array $options = []): bool
    {
        $orderId = $entity->get('order_id');
        $rows = $this->updateAll(['is_deleted' => true], ['order_id' => $orderId]);
        if ($rows < 1) {
            return false;
        }

        $this->ArtworkVariantOrders->updateAll(['is_deleted' => true], ['order_id' => $orderId]);
        $this->Payments->updateAll(['is_deleted' => true], ['order_id' => $orderId]);

        return true;
    }

    /**
     * Generates an Order ID in the format "O-AB12345".
     *
     * @return string
     * @throws \Random\RandomException
     */
    private function _generateOrderId(): string
    {
        do {
            $letters = '';
            for ($i = 0; $i < 2; $i++) {
                $letters .= chr(random_int(65, 90));
            }
            $digits = str_pad((string)random_int(0, 99999), 5, '0', STR_PAD_LEFT);
            $orderId = 'O-' . $letters . $digits;
        } while ($this->exists(['order_id' => $orderId]));

        return $orderId;
    }
}
