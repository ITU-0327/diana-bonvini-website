<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * WritingServicePayments Model
 *
 * @property \App\Model\Table\WritingServiceRequestsTable&\Cake\ORM\Association\BelongsTo $WritingServiceRequests
 *
 * @method \App\Model\Entity\WritingServicePayment newEmptyEntity()
 * @method \App\Model\Entity\WritingServicePayment newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\WritingServicePayment> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\WritingServicePayment get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\WritingServicePayment findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\WritingServicePayment patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\WritingServicePayment> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\WritingServicePayment|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\WritingServicePayment saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\WritingServicePayment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WritingServicePayment>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WritingServicePayment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WritingServicePayment> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WritingServicePayment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WritingServicePayment>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WritingServicePayment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WritingServicePayment> deleteManyOrFail(iterable $entities, array $options = [])
 */
class WritingServicePaymentsTable extends Table
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

        $this->setTable('writing_service_payments');
        $this->setDisplayField('writing_service_payment_id');
        $this->setPrimaryKey('writing_service_payment_id');

        $this->belongsTo('WritingServiceRequests', [
            'foreignKey' => 'writing_service_request_id',
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
            ->scalar('writing_service_request_id')
            ->maxLength('writing_service_request_id', 9)
            ->notEmptyString('writing_service_request_id');

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
            ->notEmptyDateTime('payment_date');

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
        $rules->add($rules->existsIn(['writing_service_request_id'], 'WritingServiceRequests'), ['errorField' => 'writing_service_request_id']);

        return $rules;
    }
}
