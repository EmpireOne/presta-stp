<?php

class Address extends AddressCore
{







    public function isDifferent($address_new)
    {
        // verification keys: VK##2
        $result = false;
        if (
            $this->firstname != $address_new->firstname ||
            $this->lastname != $address_new->lastname ||
            $this->address1 != $address_new->address1 ||
            $this->postcode != $address_new->postcode ||
            $this->city != $address_new->city
        )
            $result = true;
        return $result;
    }

}
