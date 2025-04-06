<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Event\EventInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use ArrayObject;

/**
 * WritingServiceRequests Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\RequestMessagesTable&\Cake\ORM\Association\HasMany $RequestMessages
 * @method \App\Model\Entity\WritingServiceRequest newEmptyEntity()
 * @method \App\Model\Entity\WritingServiceRequest newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\WritingServiceRequest> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\WritingServiceRequest get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\WritingServiceRequest findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\WritingServiceRequest patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\WritingServiceRequest> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\WritingServiceRequest|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\WritingServiceRequest saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\WritingServiceRequest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WritingServiceRequest>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WritingServiceRequest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WritingServiceRequest> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WritingServiceRequest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WritingServiceRequest>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WritingServiceRequest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WritingServiceRequest> deleteManyOrFail(iterable $entities, array $options = [])
 */
class WritingServiceRequestsTable extends Table
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

        $this->setTable('writing_service_requests');
        $this->setDisplayField('service_type');
        $this->setPrimaryKey('writing_service_request_id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);

        $this->hasMany('RequestMessages', [
            'foreignKey' => 'writing_service_request_id',
            'sort' => ['RequestMessages.created_at' => 'DESC'],
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
            ->scalar('service_type')
            ->requirePresence('service_type', 'create')
            ->notEmptyString('service_type');

        $validator
            ->scalar('word_count_range')
            ->maxLength('word_count_range', 50)
            ->requirePresence('word_count_range', 'create')
            ->notEmptyString('word_count_range');

        $validator
            ->scalar('notes')
            ->maxLength('notes', 100)
            ->allowEmptyString('notes');

        $validator
            ->decimal('final_price')
            ->allowEmptyString('final_price');

        $validator
            ->scalar('request_status')
            ->notEmptyString('request_status');

        $validator
            ->notEmptyString('is_deleted');

        $validator
            ->dateTime('created_at')
            ->notEmptyDateTime('created_at');

        $validator
            ->dateTime('updated_at')
            ->notEmptyDateTime('updated_at');

        $validator
            ->scalar('document')
            ->maxLength('document', 255)
            ->allowEmptyString('document');

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
     * Generates a new writing service request ID in the format "O-####" if none exists.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @param \Cake\Datasource\EntityInterface $entity The entity instance.
     * @param \ArrayObject<string, mixed> $options The options for the save.
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if ($entity->isNew() && empty($entity->writing_service_request_id)) {
            // Get the most recent writing service request, ordered by creation date
            $lastRequest = $this->find()
                ->select(['writing_service_request_id'])
                ->order(['created_at' => 'DESC'])
                ->first();

            // Extract the numeric part from the previous ID, or start at 0 if none exists.
            $lastNumber = $lastRequest ? (int)substr($lastRequest->writing_service_request_id, 2) : 0;

            // Generate a new writing service request ID in the format "O-####"
            $entity->writing_service_request_id = sprintf("O-%04d", $lastNumber + 1);
        }
    }
}
