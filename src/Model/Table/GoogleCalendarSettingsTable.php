<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * GoogleCalendarSettings Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @method \App\Model\Entity\GoogleCalendarSetting newEmptyEntity()
 * @method \App\Model\Entity\GoogleCalendarSetting newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\GoogleCalendarSetting> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\GoogleCalendarSetting get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\GoogleCalendarSetting findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\GoogleCalendarSetting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\GoogleCalendarSetting> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\GoogleCalendarSetting|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\GoogleCalendarSetting saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\GoogleCalendarSetting>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\GoogleCalendarSetting>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\GoogleCalendarSetting>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\GoogleCalendarSetting> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\GoogleCalendarSetting>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\GoogleCalendarSetting>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\GoogleCalendarSetting>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\GoogleCalendarSetting> deleteManyOrFail(iterable $entities, array $options = [])
 */
class GoogleCalendarSettingsTable extends Table
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

        $this->setTable('google_calendar_settings');
        $this->setDisplayField('setting_id');
        $this->setPrimaryKey('setting_id');

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
            ->uuid('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('calendar_id')
            ->maxLength('calendar_id', 255)
            ->notEmptyString('calendar_id');

        $validator
            ->scalar('refresh_token')
            ->allowEmptyString('refresh_token');

        $validator
            ->scalar('access_token')
            ->allowEmptyString('access_token');

        $validator
            ->dateTime('token_expires')
            ->allowEmptyDateTime('token_expires');

        $validator
            ->boolean('is_active')
            ->notEmptyString('is_active');

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
}