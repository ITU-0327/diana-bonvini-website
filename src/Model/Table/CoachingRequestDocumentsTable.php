<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CoachingRequestDocuments Model
 *
 * @property \App\Model\Table\CoachingServiceRequestsTable&\Cake\ORM\Association\BelongsTo $CoachingServiceRequests
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\CoachingRequestDocument newEmptyEntity()
 * @method \App\Model\Entity\CoachingRequestDocument newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\CoachingRequestDocument[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CoachingRequestDocument get($primaryKey, $options = [])
 * @method \App\Model\Entity\CoachingRequestDocument findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\CoachingRequestDocument patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CoachingRequestDocument[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CoachingRequestDocument|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CoachingRequestDocument saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CoachingRequestDocument[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CoachingRequestDocument[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\CoachingRequestDocument[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CoachingRequestDocument[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class CoachingRequestDocumentsTable extends Table
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

        $this->setTable('coaching_request_documents');
        $this->setDisplayField('document_name');
        $this->setPrimaryKey('coaching_request_document_id');

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
            ->uuid('coaching_request_document_id')
            ->allowEmptyString('coaching_request_document_id', null, 'create');

        $validator
            ->scalar('coaching_service_request_id')
            ->maxLength('coaching_service_request_id', 9)
            ->notEmptyString('coaching_service_request_id');

        $validator
            ->uuid('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('document_path')
            ->maxLength('document_path', 255)
            ->notEmptyString('document_path');

        $validator
            ->scalar('document_name')
            ->maxLength('document_name', 255)
            ->notEmptyString('document_name');

        $validator
            ->scalar('file_type')
            ->maxLength('file_type', 100)
            ->notEmptyString('file_type');

        $validator
            ->integer('file_size')
            ->greaterThanOrEqual('file_size', 0)
            ->notEmptyString('file_size');

        $validator
            ->scalar('uploaded_by')
            ->maxLength('uploaded_by', 50)
            ->notEmptyString('uploaded_by');

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
        $rules->add($rules->existsIn(['coaching_service_request_id'], 'CoachingServiceRequests'), ['errorField' => 'coaching_service_request_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
} 