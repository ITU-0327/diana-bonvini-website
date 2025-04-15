<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ContentBlocks Model
 *
 * @method \App\Model\Entity\ContentBlock newEmptyEntity()
 * @method \App\Model\Entity\ContentBlock newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ContentBlock> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ContentBlock get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ContentBlock findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ContentBlock patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ContentBlock> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ContentBlock|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ContentBlock saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ContentBlock>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ContentBlock>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ContentBlock>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ContentBlock> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ContentBlock>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ContentBlock>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ContentBlock>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ContentBlock> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ContentBlocksTable extends Table
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

        $this->setTable('content_blocks');
        $this->setDisplayField('display_field');
        $this->setPrimaryKey('content_block_id');
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
            ->scalar('parent')
            ->maxLength('parent', 100)
            ->allowEmptyString('parent');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 255)
            ->requirePresence('slug', 'create')
            ->notEmptyString('slug')
            ->add('slug', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('label')
            ->maxLength('label', 255)
            ->requirePresence('label', 'create')
            ->notEmptyString('label');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->scalar('type')
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

        $validator
            ->scalar('value')
            ->maxLength('value', 4294967295)
            ->allowEmptyString('value');

        $validator
            ->scalar('previous_value')
            ->maxLength('previous_value', 4294967295)
            ->allowEmptyString('previous_value');

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
        $rules->add($rules->isUnique(['slug']), ['errorField' => 'slug']);

        return $rules;
    }
}
