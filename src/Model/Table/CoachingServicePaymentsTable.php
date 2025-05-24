<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CoachingServicePayments Model
 *
 * @property \App\Model\Table\CoachingServiceRequestsTable&\Cake\ORM\Association\BelongsTo $CoachingServiceRequests
 *
 * @method \App\Model\Entity\CoachingServicePayment newEmptyEntity()
 * @method \App\Model\Entity\CoachingServicePayment newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\CoachingServicePayment> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CoachingServicePayment get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\CoachingServicePayment findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\CoachingServicePayment patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\CoachingServicePayment> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CoachingServicePayment|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\CoachingServicePayment saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\CoachingServicePayment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CoachingServicePayment>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CoachingServicePayment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CoachingServicePayment> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CoachingServicePayment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CoachingServicePayment>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CoachingServicePayment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CoachingServicePayment> deleteManyOrFail(iterable $entities, array $options = [])
 */
class CoachingServicePaymentsTable extends Table
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

        $this->setTable('coaching_service_payments');
        $this->setDisplayField('coaching_service_payment_id');
        $this->setPrimaryKey('coaching_service_payment_id');

        $this->belongsTo('CoachingServiceRequests', [
            'foreignKey' => 'coaching_service_request_id',
            'joinType' => 'INNER',
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
            ->uuid('coaching_service_payment_id')
            ->allowEmptyString('coaching_service_payment_id', null, 'create');

        $validator
            ->scalar('coaching_service_request_id')
            ->maxLength('coaching_service_request_id', 9)
            ->notEmptyString('coaching_service_request_id');

        $validator
            ->decimal('amount')
            ->requirePresence('amount', 'create')
            ->notEmptyString('amount');

        $validator
            ->scalar('transaction_id')
            ->maxLength('transaction_id', 255)
            ->allowEmptyString('transaction_id');

        $validator
            ->dateTime('payment_date')
            ->allowEmptyDateTime('payment_date');

        $validator
            ->scalar('payment_method')
            ->maxLength('payment_method', 255)
            ->notEmptyString('payment_method');

        $validator
            ->scalar('status')
            ->maxLength('status', 255)
            ->notEmptyString('status');

        $validator
            ->boolean('is_deleted')
            ->allowEmptyString('is_deleted');

        $validator
            ->dateTime('created_at')
            ->allowEmptyDateTime('created_at');

        $validator
            ->dateTime('updated_at')
            ->allowEmptyDateTime('updated_at');

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
        $rules->add($rules->existsIn(['coaching_service_request_id'], 'CoachingServiceRequests'), ['errorField' => 'coaching_service_request_id']);

        return $rules;
    }
} 