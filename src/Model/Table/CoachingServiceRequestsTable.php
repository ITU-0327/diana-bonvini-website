<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\Event;
use ArrayObject;

/**
 * CoachingServiceRequests Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\CoachingRequestMessagesTable&\Cake\ORM\Association\HasMany $CoachingRequestMessages
 * @property \App\Model\Table\CoachingServicePaymentsTable&\Cake\ORM\Association\HasMany $CoachingServicePayments
 *
 * @method \App\Model\Entity\CoachingServiceRequest newEmptyEntity()
 * @method \App\Model\Entity\CoachingServiceRequest newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\CoachingServiceRequest> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CoachingServiceRequest get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\CoachingServiceRequest findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\CoachingServiceRequest patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\CoachingServiceRequest> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CoachingServiceRequest|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\CoachingServiceRequest saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\CoachingServiceRequest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CoachingServiceRequest>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CoachingServiceRequest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CoachingServiceRequest> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CoachingServiceRequest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CoachingServiceRequest>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CoachingServiceRequest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CoachingServiceRequest> deleteManyOrFail(iterable $entities, array $options = [])
 */
class CoachingServiceRequestsTable extends Table
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

        $this->setTable('coaching_service_requests');
        $this->setDisplayField('service_type');
        $this->setPrimaryKey('coaching_service_request_id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Appointments', [
            'foreignKey' => 'appointment_id',
            'joinType' => 'LEFT',
        ]);
        
        // Add the associations
        $this->hasMany('CoachingRequestMessages', [
            'foreignKey' => 'coaching_service_request_id',
        ]);
        
        $this->hasMany('CoachingServicePayments', [
            'foreignKey' => 'coaching_service_request_id',
        ]);
        
        $this->hasMany('CoachingRequestDocuments', [
            'foreignKey' => 'coaching_service_request_id',
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
            ->uuid('appointment_id')
            ->allowEmptyString('appointment_id');

        $validator
            ->scalar('service_title')
            ->maxLength('service_title', 100)
            ->notEmptyString('service_title');

        $validator
            ->scalar('service_type')
            ->maxLength('service_type', 200)
            ->requirePresence('service_type', 'create')
            ->notEmptyString('service_type');

        $validator
            ->scalar('notes')
            ->maxLength('notes', 1000)
            ->allowEmptyString('notes');

        $validator
            ->decimal('final_price')
            ->allowEmptyString('final_price');

        $validator
            ->scalar('request_status')
            ->inList('request_status', ['pending', 'in_progress', 'completed', 'canceled', 'cancelled'])
            ->notEmptyString('request_status');

        $validator
            ->scalar('document')
            ->maxLength('document', 255)
            ->allowEmptyString('document')
            ->add('document', 'custom', [
                'rule' => function ($value, $context) {
                    // Simply allow any string or null value
                    return is_string($value) || is_null($value);
                },
                'message' => 'Document must be a valid file path'
            ]);

        $validator
            ->boolean('is_deleted')
            ->notEmptyString('is_deleted');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        
        // Only check appointment_id if it's set
        $rules->add(
            function ($entity, $options) {
                return empty($entity->appointment_id) || 
                       $this->Appointments->exists(['appointment_id' => $entity->appointment_id]);
            },
            'appointmentExists',
            ['errorField' => 'appointment_id', 'message' => __('Invalid appointment.')]
        );

        return $rules;
    }

    /**
     * Built-in CakePHP event for before save operations
     *
     * @param \Cake\Event\Event $event The event being processed
     * @param \App\Model\Entity\CoachingServiceRequest $entity The entity being saved
     * @param \ArrayObject $options The options passed to the save method
     * @return void
     */
    public function beforeSave(\Cake\Event\Event $event, $entity, \ArrayObject $options): void
    {
        // Ensure entity has a coaching_service_request_id
        if (empty($entity->coaching_service_request_id)) {
            $entity->initializeCoachingServiceRequestId();
        }
        
        // Set default values for required fields if not provided
        if (!isset($entity->request_status)) {
            $entity->request_status = 'pending';
        }
        
        if (!isset($entity->is_deleted)) {
            $entity->is_deleted = false;
        }
    }
} 