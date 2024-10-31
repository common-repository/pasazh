<?php

class PASAZHAPIPasazhProduct
{

    public $id;
    public $name;
    public $description;
    public $technical_description;
//    public $group_id;
//    public $group_name;
    public $price;
    public $percent_discount;
//    public $bargain_status;
//    public $in_town_transmission_status;
//    public $out_town_transmission_status;
//    public $in_town_transmission_fix_price;
//    public $out_town_transmission_fix_price;
    public $visibility_status;
    public $quantity;
    public $weight;
//    public $gift;
//    public $transmission_duration;
    public $images;
    public $specifications;
//    public $type;
    public $url;
    public $commodity_id;



    /**
     * @return mixed
     */
    public function getGuarantee()
    {
        return $this->guarantee;
    }

    /**
     * @param mixed $guarantee
     */
    public function setGuarantee($guarantee): void
    {
        $this->guarantee = $guarantee;
    }


    /**
     * @return mixed
     */
    public function getCommodityId()
    {
        return $this->commodity_id;
    }

    /**
     * @param mixed $commodity_id
     */
    public function setCommodityId($commodity_id): void
    {
        $this->commodity_id = $commodity_id;
    }



    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url): void
    {
        $this->url = $url;
    }


    public static function parse_woo_product($object)
    {
        /** @var PASAZHAPIPasazhProduct $pasazh_product */
        $pasazh_product = new PASAZHAPIPasazhProduct();
        $pasazh_product->setId($object->id);
        $pasazh_product->setName($object->name);
        $pasazh_product->setDescription(str_replace('\n\n', '\n', strip_tags($object->description)));
        $pasazh_product->setPrice($object->regular_price);
        if ($object->regular_price != $object->sale_price) {
            // calculate and set percent discount
            // 125000  100000
            $percent_discount = 100 - ((100 * $object->sale_price) / $object->regular_price);
            $percent_discount = floor($percent_discount);
            $percent_discount = (int)$percent_discount;
            $pasazh_product->setPercentDiscount($percent_discount);
        }
        $pasazh_product->setQuantity(1);
        $arr_images = [];
        foreach ($object->images as $image) {
            $arr_images [] = $image->src;
        }
        $pasazh_product->setImages($arr_images);
        $pasazh_product->setGroupId(1);
        $group_name = '';
        foreach ($object->categories as $cat) {
            $group_name .= $cat->name . ' - ';
        }
        $pasazh_product->setGroupName($group_name);

        $pasazh_product->setBargainStatus(0);

        $tecnical = '';
        foreach ($object->attributes as $attr) {
            $name = str_replace('-', ' ', $attr->name);

            $tecnical .= $name . ': ';
            foreach ($attr->options as $option) {
                $tecnical .= $option . ' ';
            }

            $tecnical .= "\n";
        }
        $pasazh_product->setTechnicalDescription($tecnical);

        return $pasazh_product;

    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }


    /**
     * @return mixed
     */
    public function getSpecifications()
    {
        return $this->specifications;
    }

    /**
     * @param mixed $specifications
     */
    public function setSpecifications($specifications): void
    {
        $this->specifications = $specifications;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {

        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getTechnicalDescription()
    {
        try{
            return $this->technical_description;
        }catch (Exception $e){
            return null;
        }
    }

    /**
     * @param mixed $technical_description
     */
    public function setTechnicalDescription($technical_description): void
    {
        $this->technical_description = $technical_description;
    }

    /**
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * @param mixed $group_id
     */
    public function setGroupId($group_id): void
    {
        $this->group_id = $group_id;
    }

    /**
     * @return mixed
     */
    public function getGroupName()
    {
        return $this->group_name;
    }

    /**
     * @param mixed $group_name
     */
    public function setGroupName($group_name): void
    {
        $this->group_name = $group_name;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        if (isset($this->price))
            return $this->price;
        else
            return null;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getPercentDiscount()
    {
        return $this->percent_discount;
    }

    /**
     * @param mixed $percent_discount
     */
    public function setPercentDiscount($percent_discount): void
    {
        $this->percent_discount = $percent_discount;
    }

    /**
     * @return mixed
     */
    public function getBargainStatus()
    {
        return $this->bargain_status;
    }

    /**
     * @param mixed $bargain_status
     */
    public function setBargainStatus($bargain_status): void
    {
        $this->bargain_status = $bargain_status;
    }

    /**
     * @return mixed
     */
    public function getInTownTransmissionStatus()
    {
        return $this->in_town_transmission_status;
    }

    /**
     * @param mixed $in_town_transmission_status
     */
    public function setInTownTransmissionStatus($in_town_transmission_status): void
    {
        $this->in_town_transmission_status = $in_town_transmission_status;
    }

    /**
     * @return mixed
     */
    public function getOutTownTransmissionStatus()
    {
        return $this->out_town_transmission_status;
    }

    /**
     * @param mixed $out_town_transmission_status
     */
    public function setOutTownTransmissionStatus($out_town_transmission_status): void
    {
        $this->out_town_transmission_status = $out_town_transmission_status;
    }

    /**
     * @return mixed
     */
    public function getInTownTransmissionFixPrice()
    {
        return $this->in_town_transmission_fix_price;
    }

    /**
     * @param mixed $in_town_transmission_fix_price
     */
    public function setInTownTransmissionFixPrice($in_town_transmission_fix_price): void
    {
        $this->in_town_transmission_fix_price = $in_town_transmission_fix_price;
    }

    /**
     * @return mixed
     */
    public function getOutTownTransmissionFixPrice()
    {
        return $this->out_town_transmission_fix_price;
    }

    /**
     * @param mixed $out_town_transmission_fix_price
     */
    public function setOutTownTransmissionFixPrice($out_town_transmission_fix_price): void
    {
        $this->out_town_transmission_fix_price = $out_town_transmission_fix_price;
    }

    /**
     * @return mixed
     */
    public function getVisibilityStatus()
    {
        return $this->visibility_status;
    }

    /**
     * @param mixed $visibility_status
     */
    public function setVisibilityStatus($visibility_status): void
    {
        $this->visibility_status = $visibility_status;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        try {
            return $this->weight;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param mixed $weight
     */
    public function setWeight($weight): void
    {
        $this->weight = $weight;
    }

    /**
     * @return mixed
     */
    public function getGift()
    {
        try{
            return $this->gift;
        }catch (Exception $e){
            return null;
        }
    }

    /**
     * @param mixed $gift
     */
    public function setGift($gift): void
    {
        $this->gift = $gift;
    }

    /**
     * @return mixed
     */
    public function getTransmissionDuration()
    {
        return $this->transmission_duration;
    }

    /**
     * @param mixed $transmission_duration
     */
    public function setTransmissionDuration($transmission_duration): void
    {
        $this->transmission_duration = $transmission_duration;
    }

    /**
     * @return mixed
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param mixed $images
     */
    public function setImages($images): void
    {
        $this->images = $images;
    }

    public function validate_create()
    {

        if (!isset($this->id)) throw new Exception("product id not set");
        if (!isset($this->name)) throw new Exception("product name not set");
        if (!isset($this->description)) throw new Exception("product description not set");
        if (!isset($this->group_id)) throw new Exception("product group_id not set");
        if (!isset($this->group_name)) throw new Exception("product group_name not set");
        if (!isset($this->price)) throw new Exception("product price not set");
        if (!isset($this->images)) throw new Exception("product images not set");
        if (!is_array($this->images)) throw new Exception("product images should be an array of urls");

    }

    public function validate_edit()
    {

        if (!isset($this->id)) throw new Exception("product id not set");
    }

    public function validate_delete()
    {

        if (!isset($this->id)) throw new Exception("product id not set");
    }

    public function setRawData($raw_data)
    {
        $this->raw_data = $raw_data;
    }

}
