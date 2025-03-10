<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Artworks Model
 *
 * @method \App\Model\Entity\Artwork newEmptyEntity()
 * @method \App\Model\Entity\Artwork newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Artwork> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Artwork get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Artwork findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Artwork patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Artwork> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Artwork|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Artwork saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Artwork>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Artwork>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Artwork>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Artwork> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Artwork>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Artwork>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Artwork>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Artwork> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ArtworksTable extends Table
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

        $this->setTable('artworks');
        $this->setDisplayField('title');
        $this->setPrimaryKey('artwork_id');
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
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->scalar('image_path')
            ->maxLength('image_path', 255)
            ->requirePresence('image_path', 'create')
            ->notEmptyString('image_path');

        $validator
            ->decimal('price')
            ->requirePresence('price', 'create')
            ->notEmptyString('price');

        $validator
            ->scalar('availability_status')
            ->requirePresence('availability_status', 'create')
            ->notEmptyString('availability_status');

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
}
