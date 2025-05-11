<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Artwork;
use App\Service\R2StorageService;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Artworks Model
 *
 * @property \App\Model\Table\ArtworkVariantsTable&\Cake\ORM\Association\HasMany $ArtworkVariants
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
    private R2StorageService $r2StorageService;

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->r2StorageService = new R2StorageService();

        $this->setTable('artworks');
        $this->setDisplayField('title');
        $this->setPrimaryKey('artwork_id');

        $this->hasMany('ArtworkVariants', [
            'foreignKey' => 'artwork_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
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
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->add('image_path', 'fileSize', [
                'rule' => ['fileSize', '<=', '8MB'],
                'message' => 'Image must be 8 MB or smaller.',
            ])
            ->add('image_path', 'mimeType', [
                'rule' => ['mimeType', ['image/jpeg']],
                'message' => 'Only JPEG images allowed.',
            ]);

        $validator
            ->scalar('availability_status')
            ->requirePresence('availability_status', 'create')
            ->notEmptyString('availability_status');

        $validator
            ->integer('max_copies')
            ->notEmptyString('max_copies');

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
     * Override delete() to implement soft-delete with clear success/failure.
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to delete.
     * @param array<string, mixed> $options Options passed from controller (e.g. ['atomic' => true]).
     * @return bool True on success (soft- or hard-delete), false on failure.
     */
    public function delete(EntityInterface $entity, array $options = []): bool
    {
        $artworkId = $entity->get('artwork_id');
        // Fetch all variant IDs for this artwork
        $variantIds = $this->ArtworkVariants->find('list', [
            'keyField' => 'artwork_variant_id',
            'valueField' => 'artwork_variant_id',
        ])
        ->where(['artwork_id' => $artworkId])
        ->toArray();

        // Remove any variant entries in carts
        $this->ArtworkVariants->ArtworkVariantCarts->deleteAll([
            'artwork_variant_id IN' => $variantIds,
        ]);

        // Check if any orders exist for these variants
        $hasOrders = !empty($variantIds) &&
            $this->ArtworkVariants->ArtworkVariantOrders->exists([
                'artwork_variant_id IN' => $variantIds,
            ]);

        if ($hasOrders) {
            // Soft-delete the artwork
            $rows = $this->updateAll(
                ['is_deleted' => true],
                ['artwork_id' => $artworkId],
            );
            if (!$rows) {
                return false;
            }

            // Soft-delete its variants
            $this->ArtworkVariants->updateAll(
                ['is_deleted' => true],
                ['artwork_id' => $artworkId],
            );

            return true;
        }

        // No dependent orders: proceed with hard delete
        return parent::delete($entity, $options);
    }

    /**
     * After a hard delete succeeds, remove the object from R2.
     *
     * @param \Cake\Event\EventInterface<\App\Model\Entity\Artwork> $event The afterDelete event.
     * @param \App\Model\Entity\Artwork $entity The deleted artwork entity.
     * @param \ArrayObject<string,mixed> $options Options passed to delete.
     * @return void
     */
    public function afterDelete(EventInterface $event, Artwork $entity, ArrayObject $options): void
    {
        $key = "{$entity->artwork_id}_wm.jpg";

        $this->r2StorageService->delete($key);
    }
}
