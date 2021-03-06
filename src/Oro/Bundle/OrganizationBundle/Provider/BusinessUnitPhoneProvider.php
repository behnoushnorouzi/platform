<?php

namespace Oro\Bundle\OrganizationBundle\Provider;

use Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;

class BusinessUnitPhoneProvider implements PhoneProviderInterface
{
    /**
     * Gets a phone number of the given BusinessUnit object
     *
     * @param BusinessUnit $object
     *
     * @return string|null
     */
    public function getPhoneNumber($object)
    {
        return $object->getPhone();
    }

    /**
     * Gets a list of all phone numbers available for the given BusinessUnit object
     *
     * @param BusinessUnit $object
     *
     * @return array of [phone number, phone owner]
     */
    public function getPhoneNumbers($object)
    {
        $result = [];

        $phone = $object->getPhone();
        if (!empty($phone)) {
            $result[] = [$phone, $object];
        }

        return $result;
    }
}
