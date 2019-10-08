<?php

include_once 'Bags.php';
include_once 'YmlDocument.php';

class YmlGenerator
{
    /** @var YmlDocument */
    private $ymlFile;
    /** @var string */
    private $outputFile;
    /**
     * @var bool
     */
    private $validate;

    public function __construct($outputFile, $validate = true)
    {
        $this->outputFile = $outputFile;
        $this->validate = $validate;
    }

    public function generate()
    {
        $this->generateBasicInfo();
        $this->generateCategories();
        $this->generateGoods();
        $this->finishGeneration();
        echo "Сгенерированный файл сохранен как {$this->outputFile}";

        if ($this->validate) {
            if ($this->validateYml()) {
                echo "Полученный файл {$this->outputFile} прошел проверку на валидность";
            } else {
                echo "Полученный файл {$this->outputFile} не прошел проверку на валидность!";
            }
        }
    }

    private function generateBasicInfo()
    {
        $this->ymlFile = new YmlDocument(Bags::SHORT_SHOP_NAME, Bags::FULL_SHOP_NAME);

        $this->ymlFile->fileName($this->outputFile)->bufferSize(1024 * 1024 * 16);
        $this->ymlFile->url(Bags::SHOP_URL);
        $this->ymlFile->cms(Bags::CMS_NAME, Bags::CMS_VERSION);
        $this->ymlFile->agency(Bags::CMS_AGENCY);
        $this->ymlFile->email(Bags::CMS_AGENCY_EMAIL);
        $this->ymlFile->currency(Bags::DEFAULT_CURRENCY, Bags::DEFAULT_CURRENCY_RATE);
    }

    private function generateCategories()
    {
        $categories = $this->getCategories();
        foreach ($categories as $categoryInfo) {
            if (isset($categoryInfo['parent'])) {
                $this->ymlFile->category($categoryInfo['id'], $categoryInfo['name'], $categoryInfo['parentId']);
            } else {
                $this->ymlFile->category($categoryInfo['id'], $categoryInfo['name']);
            }
        }
    }

    private function generateGoods()
    {
        $goods = $this->getGoods();
        foreach ($goods as $goodsInfo) {
            $offer = $this->ymlFile->simple($goodsInfo['name'], $goodsInfo['id'], $goodsInfo['price'],Bags::DEFAULT_CURRENCY, $goodsInfo['categoryId']);
            $offer
                ->model($goodsInfo['model'])
                ->vendor($goodsInfo['vendor'])
                ->vendorCode($goodsInfo['vendorCode'])
                ->available($goodsInfo['isAvailable'])
                ->url($goodsInfo['url'])
                ->description($goodsInfo['description'])
                ->origin($goodsInfo['origin']);

            $images = $this->getImagesForOffer($goodsInfo['id']);
            foreach ($images as $imageUrl) {
                $offer->pic($imageUrl);
            }
        }
    }

    private function finishGeneration()
    {
        unset($this->ymlFile);
    }

    private function getCategories()
    {
        return [];
    }

    private function getGoods()
    {
        return [];
    }

    private function getImagesForOffer($id)
    {
        return [];
    }

    private function validateYml()
    {
        $checker = new DOMDocument('1.0', "UTF-8");
        $checker->load($this->outputFile);
        $valid = $checker->schemaValidate('./shops_with_byn.xsd');
        return $valid;
    }
}