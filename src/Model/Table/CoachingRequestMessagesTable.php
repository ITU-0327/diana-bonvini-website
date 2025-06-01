<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CoachingRequestMessages Model
 *
 * @property \App\Model\Table\CoachingServiceRequestsTable&\Cake\ORM\Association\BelongsTo $CoachingServiceRequests
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\CoachingRequestMessage newEmptyEntity()
 * @method \App\Model\Entity\CoachingRequestMessage newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\CoachingRequestMessage[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CoachingRequestMessage get($primaryKey, $options = [])
 * @method \App\Model\Entity\CoachingRequestMessage findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\CoachingRequestMessage patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CoachingRequestMessage[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CoachingRequestMessage|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CoachingRequestMessage saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CoachingRequestMessage[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CoachingRequestMessage[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\CoachingRequestMessage[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CoachingRequestMessage[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class CoachingRequestMessagesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('coaching_request_messages');
        $this->setDisplayField('coaching_request_message_id');
        $this->setPrimaryKey('coaching_request_message_id');

        // Add Timestamp behavior to handle created_at and updated_at automatically
        $this->addBehavior('Timestamp');

        $this->belongsTo('CoachingServiceRequests', [
            'foreignKey' => 'coaching_service_request_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
            ->scalar('coaching_service_request_id')
            ->maxLength('coaching_service_request_id', 9)
            ->notEmptyString('coaching_service_request_id');

        $validator
            ->uuid('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('message')
            ->requirePresence('message', 'create')
            ->notEmptyString('message');

        $validator
            ->boolean('is_read')
            ->notEmptyString('is_read');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
} 