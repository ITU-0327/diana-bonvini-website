<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Event\EventInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use ArrayObject;

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

    /**
     * Before save callback.
     *
     * Generates a new artwork ID in the format "A###" if none exists.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @param \Cake\Datasource\EntityInterface $entity The entity instance.
     * @param \ArrayObject<string, mixed> $options The options for the save.
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if ($entity->isNew() && empty($entity->artwork_id)) {
            // Get the most recent artwork, ordered by creation date.
            $lastArtwork = $this->find()
                ->select(['artwork_id'])
                ->order(['created_at' => 'DESC'])
                ->first();

            // Extract the numeric part from the previous artwork ID, or start at 0 if none exists.
            $lastNumber = $lastArtwork ? (int)substr($lastArtwork->artwork_id, 1) : 0;

            // Generate a new artwork ID in the format "A###" (e.g., A001, A002, etc.)
            $entity->artwork_id = sprintf("A%03d", $lastNumber + 1);
        }
    }
}
