<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ArtworkCarts Model
 *
 * @property \App\Model\Table\CartsTable&\Cake\ORM\Association\BelongsTo $Carts
 * @property \App\Model\Table\ArtworksTable&\Cake\ORM\Association\BelongsTo $Artworks
 *
 * @method \App\Model\Entity\ArtworkCart newEmptyEntity()
 * @method \App\Model\Entity\ArtworkCart newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ArtworkCart> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ArtworkCart get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ArtworkCart findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ArtworkCart patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ArtworkCart> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ArtworkCart|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ArtworkCart saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkCart>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkCart>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkCart>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkCart> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkCart>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkCart>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkCart>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkCart> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ArtworkCartsTable extends Table
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

        $this->setTable('artwork_carts');
        $this->setDisplayField('artwork_cart_id');
        $this->setPrimaryKey('artwork_cart_id');

        $this->belongsTo('Carts', [
            'foreignKey' => 'cart_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Artworks', [
            'foreignKey' => 'artwork_id',
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
            ->uuid('cart_id')
            ->notEmptyString('cart_id');

        $validator
            ->uuid('artwork_id')
            ->notEmptyString('artwork_id');

        $validator
            ->integer('quantity')
            ->notEmptyString('quantity');

        $validator
            ->dateTime('date_added')
            ->notEmptyDateTime('date_added');

        $validator
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
        $rules->add($rules->existsIn(['cart_id'], 'Carts'), ['errorField' => 'cart_id']);
        $rules->add($rules->existsIn(['artwork_id'], 'Artworks'), ['errorField' => 'artwork_id']);

        return $rules;
    }
}
