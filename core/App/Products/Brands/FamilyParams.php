<?php


namespace App\Products\Brands;


use Novut\Core\Query;
use Novut\Db\OrmGroup;
use Novut\Db\OrmOptions;
use Novut\Db\OrmOptionsGroup;

class FamilyParams
{
    use OrmGroup;

    static function defaultORMOptions(): OrmOptions
    {
        return (new OrmOptions())
            ->setTableName('inv_products_brands_family_params')
            ->setPrimaryKey('BFParamID')
            ->setFieldName('BFParamID')
            ->setCancelled(true, true)
            ->setClass(FamilyParam::class);
        ;
    }

    static public function update(Family $family)
    {
        $param_resume = [];
        foreach (FamilyParams::list($family) as $f_param)
        {
            $param = new Param((new Brand($family->getBrandID())), $f_param->getBParamID());
            $param_resume[$f_param->getBParamID()] = ['id' => $param->getBParamID(), 'name' => $param->getBParamName(), 'code' => $param->getBParamCode()];
        }

        $db = FamilyParams::getORMOptions()->getDb();
        $db->Update(Families::getORMOptions()->getTableName(), ['BFamilyParams' => $param_resume ? \BN_Coders::json_encode($param_resume) : ""], 'BFamilyID', $family->getBFamilyID());
    }

    static public function param_exist(Family $family, $BParamID)
    {

        $f_param = new FamilyParam($family);
        $query = new Query();

        $query->setWhereField('BParamID');
        $query->setWhereValue($BParamID);

        $query->addQuery(" AND BParamID = :BParamID AND BrandID = :BrandID AND BFamilyID = :BFamilyID AND Cancelled != 1");
        $query->addParam('BrandID', $family->getBrandID());
        $query->addParam('BFamilyID', $family->getBFamilyID());
        $query->addParam('BParamID', $BParamID);

        $f_param->find_by_query($query);

        if ($f_param->getBFParamID())
        {
            return $f_param;
        }
        else
        {
            return null;
        }

    }


    /**
     * @param $family
     * @param Query|null $query
     * @param OrmOptionsGroup|null $options
     * @return array|FamilyParam[]
     */
    static function list($family, Query $query = null, OrmOptionsGroup $options = null): array
    {

        $family = $family && $family instanceof Family ? $family : new Family(is_numeric($family) ? $family : null);

        if (!$family instanceof Family || !$family->getBFamilyID())
        {
            return [];
        }

        $options = $options ? : new OrmOptionsGroup();
        $options->setItemClass(function ($data) use ($family)
        {
            $item = new FamilyParam($family);
            $item->import($data);
            return $item;
        });

        $query = $query ? : new Query();
        $query->addQuery(" AND BFamilyID = :BFamilyID");
        $query->addParam('BFamilyID', $family->getBFamilyID());
        $query->setOrder('RowOrder', "ASC");

        return self::_list($query, $options);

    }


    static function sort_items(Family $family, $items)
    {
        $options = self::getORMOptions();
        $db = $options->getDb();

        if ($items && is_array($items))
        {
            $order = 0;
            foreach ($items as $BFParamID)
            {
                $order++;
                $db->Update($options->getTableName(), ['RowOrder' => $order], 'BFParamID', $BFParamID, " AND BFamilyID = :BFamilyID AND Cancelled = 0", ['BFamilyID' => $family->getBFamilyID()]);
            }

            self::update($family);

        }
    }

}