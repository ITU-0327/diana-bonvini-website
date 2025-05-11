<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ArtworkVariants Model
 *
 * @property \App\Model\Table\ArtworksTable&\Cake\ORM\Association\BelongsTo $Artworks
 * @property \App\Model\Table\ArtworkVariantOrdersTable&\Cake\ORM\Association\HasMany $ArtworkVariantOrders
 * @property \App\Model\Table\ArtworkVariantCartsTable&\Cake\ORM\Association\HasMany $ArtworkVariantCarts
 * @method \App\Model\Entity\ArtworkVariant newEmptyEntity()
 * @method \App\Model\Entity\ArtworkVariant newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ArtworkVariant> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ArtworkVariant get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ArtworkVariant findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ArtworkVariant patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ArtworkVariant> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ArtworkVariant|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ArtworkVariant saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkVariant>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkVariant>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkVariant>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkVariant> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkVariant>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkVariant>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkVariant>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkVariant> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ArtworkVariantsTable extends Table
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

        $this->setTable('artwork_variants');
        $this->setDisplayField('dimension');
        $this->setPrimaryKey('artwork_variant_id');

        $this->belongsTo('Artworks', [
            'foreignKey' => 'artwork_id',
            'joinType' => 'INNER',
        ]);

        $this->hasMany('ArtworkVariantOrders', [
            'foreignKey' => 'artwork_variant_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);

        $this->hasMany('ArtworkVariantCarts', [
            'foreignKey' => 'artwork_variant_id',
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
            ->uuid('artwork_id')
            ->notEmptyString('artwork_id');

        $validator
            ->scalar('dimension')
            ->requirePresence('dimension', 'create')
            ->notEmptyString('dimension')
            ->inList('dimension', ['A3','A2','A1'], 'Dimension must be A3, A2 or A1');

        $validator
            ->decimal('price')
            ->requirePresence('price', 'create')
            ->notEmptyString('price')
            ->greaterThanOrEqual('price', 1, 'Price must be at least $1.00');

        $validator
            ->boolean('is_deleted')
            ->notEmptyString('is_deleted');

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
        $rules->add($rules->existsIn(['artwork_id'], 'Artworks'), ['errorField' => 'artwork_id']);

        return $rules;
    }
}
