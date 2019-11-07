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
        echo "Сгенерированный файл сохранен как {$this->outputFile}\n";

        if ($this->validate) {
            if ($this->validateYml()) {
                echo "Полученный файл {$this->outputFile} прошел проверку на валидность\n";
            } else {
                echo "Полученный файл {$this->outputFile} не прошел проверку на валидность!\n";
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
            if (isset($categoryInfo['parentId'])) {
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
            $offer = $this->ymlFile->simple($goodsInfo['name'], $goodsInfo['id'], $goodsInfo['price'],
                Bags::DEFAULT_CURRENCY, $goodsInfo['categoryId']);
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
        $categoriesFile = __DIR__ . '/data/prepared/categories.csv';
        $handle = fopen($categoriesFile, 'rb');
        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            $categoryInfo = [
                'id' => (int)trim($data[0]),
                'name' => trim($data[2]),
            ];
            $parentId = (int)trim($data[1]);
            if ($parentId > 0) {
                $categoryInfo['parentId'] = $parentId;
            }
            yield $categoryInfo;
        }
    }

    private function getGoods()
    {
        $goodsFile = __DIR__ . '/data/prepared/goods.csv';
        $handle = fopen($goodsFile, 'rb');
        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            $goodsInfo = [
                'id' => (int)trim($data[0]),
                'name' => trim($data[2]),
                'price' => (int)trim($data[0]),
                'categoryId' => (int)trim($data[1]),
                'model' => trim($data[4]),
                'vendor' => trim($data[5]),
                'vendorCode' => trim($data[6]),
                'isAvailable' => true,
                'url' => '',
                'description' => trim($data[7]),
                'origin' => trim($data[8])
            ];
            yield $goodsInfo;
        }
    }

    private function getImagesForOffer($id)
    {
        $path = __DIR__ . '/data/images/'.$id;
        if (!is_dir($path)) {
            return [];
        }
        $directory = new \RecursiveDirectoryIterator($path);
        $iterator = new \RecursiveIteratorIterator($directory);
        $result = [];
        /** @var SplFileInfo $info */
        foreach ($iterator as $i => $info) {
            if (!is_file($info->getRealPath())) {
                continue;
            }
            $result[] = Bags::IMAGES_URL_PREFIX . '/' . $id . '/' . $info->getFilename();
            if (count($result) > 10) {
                break;
            }
        }
        return $result;
    }

    private function validateYml()
    {
        $checker = new DOMDocument('1.0', "UTF-8");
        $checker->load($this->outputFile);
        $valid = $checker->schemaValidate('./shops_with_byn.xsd');
        return $valid;
    }
}
