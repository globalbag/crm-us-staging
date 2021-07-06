<?php


namespace App\Products\Brands;


use Novut\Core\Query;
use Novut\Db\OrmGroup;
use Novut\Db\OrmOptions;
use Novut\Db\OrmOptionsGroup;

class FamilyImages
{
    use OrmGroup;

    static function defaultORMOptions(): OrmOptions
    {
        return (new OrmOptions())
            ->setTableName('inv_products_brands_family_images')
            ->setPrimaryKey('BFImageID')
            ->setFieldName('BFImageDescription')
            ->setCancelled(true, true)
            ->setClass(FamilyImage::class);
        ;
    }

    static public function update(Family $family)
    {
        $image_list = [];
        $image_defaul = "";
        foreach (FamilyImages::list($family) as $f_param)
        {
            $image_list[] = $f_param->getBFImageUrl();
            if ($f_param->getBFImageDefault())
            {
                $image_defaul = $f_param->getBFImageUrl();
            }
        }

        $update_family['BFamilyImageDefault'] = $image_defaul;
        $update_family['BFamilyImages'] = $image_list ? \BN_Coders::json_encode($image_list) : "";

        $db = FamilyImages::getORMOptions()->getDb();
        $db->Update(Families::getORMOptions()->getTableName(), $update_family, 'BFamilyID', $family->getBFamilyID());
    }


    /**
     * @param $family
     * @param Query|null $query
     * @param OrmOptionsGroup|null $options
     * @return array|FamilyImage[]
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
            $item = new FamilyImage($family);
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
            foreach ($items as $BFImageID)
            {
                $order++;
                $db->Update($options->getTableName(), ['RowOrder' => $order], 'BFImageID', $BFImageID, " AND BFamilyID = :BFamilyID AND Cancelled = 0", ['BFamilyID' => $family->getBFamilyID()]);
            }

            self::update($family);

        }
    }

    static function set_default(Family $family, $BFImageID)
    {

        if ($BFImageID)
        {
            $options = self::getORMOptions();
            $db = $options->getDb();
            $db->Update($options->getTableName(), ['BFImageDefault' => 0], 'BFamilyID', $family->getBFamilyID(), "  AND Cancelled = 0");
            $db->Update($options->getTableName(), ['BFImageDefault' => 1], 'BFImageID', $BFImageID, " AND BFamilyID = :BFamilyID AND Cancelled = 0", ['BFamilyID' => $family->getBFamilyID()]);
            self::update($family);
        }

    }

}