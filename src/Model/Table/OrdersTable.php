<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\EventInterface;
use Cake\Datasource\EntityInterface;
use ArrayObject;

/**
 * Orders Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\ArtworkOrdersTable&\Cake\ORM\Association\HasMany $ArtworkOrders
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

        $this->hasMany('ArtworkOrders', [
            'foreignKey' => 'order_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);

        $this->hasOne('Payments', [
            'foreignKey' => 'order_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
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
            ->notEmptyString('total_amount');

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
            ->notEmptyString('billing_first_name');

        $validator
            ->scalar('billing_last_name')
            ->maxLength('billing_last_name', 255)
            ->requirePresence('billing_last_name', 'create')
            ->notEmptyString('billing_last_name');

        $validator
            ->scalar('billing_company')
            ->maxLength('billing_company', 255)
            ->allowEmptyString('billing_company');

        $validator
            ->scalar('billing_email')
            ->maxLength('billing_email', 255)
            ->requirePresence('billing_email', 'create')
            ->notEmptyString('billing_email');

        $validator
            ->scalar('shipping_country')
            ->maxLength('shipping_country', 2)
            ->requirePresence('shipping_country', 'create')
            ->notEmptyString('shipping_country');

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
            ->notEmptyString('shipping_postcode');

        $validator
            ->scalar('shipping_phone')
            ->maxLength('shipping_phone', 50)
            ->requirePresence('shipping_phone', 'create')
            ->notEmptyString('shipping_phone');

        $validator
            ->scalar('order_notes')
            ->allowEmptyString('order_notes');

        $validator
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
     * Before save callback.
     *
     * Generates a new order ID in the format "O-####" if none exists.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @param \Cake\Datasource\EntityInterface $entity The entity instance.
     * @param \ArrayObject<string, mixed> $options The options for the save.
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if ($entity->isNew() && empty($entity->order_id)) {
            // Get the most recent order, ordered by creation date
            $lastOrder = $this->find()
                ->select(['order_id'])
                ->order(['created_at' => 'DESC'])
                ->first();

            // Extract the numeric part from the previous order ID, or start at 0 if none exists
            $lastNumber = $lastOrder ? (int)substr($lastOrder->order_id, 2) : 0;

            // Generate a new order ID in the format "O-####"
            $entity->order_id = sprintf("O-%04d", $lastNumber + 1);
        }
    }
}
