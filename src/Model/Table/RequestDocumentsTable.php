<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RequestDocuments Model
 *
 * @property \App\Model\Table\WritingServiceRequestsTable&\Cake\ORM\Association\BelongsTo $WritingServiceRequests
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\RequestDocument newEmptyEntity()
 * @method \App\Model\Entity\RequestDocument newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\RequestDocument> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RequestDocument get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\RequestDocument findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\RequestDocument patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\RequestDocument> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RequestDocument|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\RequestDocument saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\RequestDocument>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RequestDocument>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RequestDocument>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RequestDocument> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RequestDocument>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RequestDocument>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RequestDocument>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RequestDocument> deleteManyOrFail(iterable $entities, array $options = [])
 */
class RequestDocumentsTable extends Table
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

        $this->setTable('request_documents');
        $this->setDisplayField('document_path');
        $this->setPrimaryKey('request_document_id');

        $this->belongsTo('WritingServiceRequests', [
            'foreignKey' => 'writing_service_request_id',
            'joinType' => 'INNER',
            'bindingKey' => 'writing_service_request_id'
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
            ->uuid('writing_service_request_id')
            ->notEmptyString('writing_service_request_id');

        $validator
            ->uuid('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('document_path')
            ->maxLength('document_path', 255)
            ->requirePresence('document_path', 'create')
            ->notEmptyString('document_path');

        $validator
            ->scalar('document_name')
            ->maxLength('document_name', 255)
            ->requirePresence('document_name', 'create')
            ->notEmptyString('document_name');

        $validator
            ->scalar('file_type')
            ->maxLength('file_type', 100)
            ->requirePresence('file_type', 'create')
            ->notEmptyString('file_type');

        $validator
            ->integer('file_size')
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
            ->requirePresence('created_at', 'create')
            ->notEmptyDateTime('created_at');

        $validator
            ->dateTime('updated_at')
            ->allowEmptyDateTime('updated_at');

        $validator
            ->scalar('writing_service_request_id')
            ->notEmptyString('writing_service_request_id');

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
        // Use a custom rule to check if the writing service request exists
        $rules->add(
            function ($entity, $options) {
                return $this->WritingServiceRequests->exists(['writing_service_request_id' => $entity->writing_service_request_id]);
            },
            'writingServiceRequestExists',
            ['errorField' => 'writing_service_request_id', 'message' => __('Invalid writing service request.')]
        );
        
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    /**
     * Before save event
     *
     * @param \Cake\Event\Event $event The event
     * @param \App\Model\Entity\RequestDocument $entity The entity
     * @param \ArrayObject $options Options
     * @return void
     */
    public function beforeSave(\Cake\Event\Event $event, $entity, \ArrayObject $options): void
    {
        // Generate UUID for request_document_id if not set
        if (empty($entity->request_document_id)) {
            $entity->request_document_id = \Cake\Utility\Text::uuid();
        }
        
        // Set created_at if not set
        if (empty($entity->created_at)) {
            $entity->created_at = new \Cake\I18n\DateTime();
        }
    }
}
