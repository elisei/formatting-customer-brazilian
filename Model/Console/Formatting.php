<?php

namespace O2TI\FormattingCustomerBrazilian\Model\Console;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Customer\Api\AddressRepositoryInterface as AddressCollectionFactory;
use Magento\Customer\Model\Customer\Interceptor as CustomerInterceptor;
use Magento\Customer\Model\Data\Address;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use O2TI\FormattingCustomerBrazilian\Model\Console\AbstractModel;
use O2TI\FormattingCustomerBrazilian\Logger\Logger;

class Formatting extends AbstractModel
{
    /**
     * @var CustomerCollectionFactory
     */
    protected $customerFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteria;

    /**
     * @var AddressCollectionFactory
     */
    protected $addressFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Construct.
     *
     * @param CustomerCollectionFactory $customerFactory
     * @param SearchCriteriaBuilder     $searchCriteria
     * @param AddressCollectionFactory  $addressFactory
     * @param Logger                    $logger
     */
    public function __construct(
        CustomerCollectionFactory $customerFactory,
        SearchCriteriaBuilder $searchCriteria,
        AddressCollectionFactory $addressFactory,
        Logger $logger
    ) {
        $this->customerFactory = $customerFactory;
        $this->searchCriteria = $searchCriteria;
        $this->addressFactory = $addressFactory;
        $this->logger = $logger;
    }

    /**
     * Execute.
     *
     * @return int
     */
    public function execute()
    {
        $customerCollection = $this->customerFactory->create();
        foreach ($customerCollection as $customer) {
            $taxvat = $customer->getTaxvat();
            if ($customer->getDefaultBilling() && $taxvat) {
                $this->formattingAddress($customer);
            } elseif (!$customer->getDefaultBilling()) {
                $hasDefault = $this->setDefaultAddress($customer);
                if ($hasDefault) {
                    $this->formattingAddress($customer);
                }
            }
        }

        return 1;
    }

    /**
     * Formatting Address
     *
     * @param \Magento\Customer\Model\Customer $customer
     */
    public function formattingAddress($customer)
    {
        $customerId = $customer->getId();
        $email = $customer->getEmail();
        $taxvat = $customer->getTaxvat();
        $searchCriteria = $this->searchCriteria->addFilter('parent_id', $customerId)->create();
        $addressRepository = $this->addressFactory->getList($searchCriteria);
        foreach ($addressRepository->getItems() as $address) {
            if ($address->getCountryId() === "BR") {
                $vatValidate = isset($taxvat) ? $this->setVatIdInAddress($taxvat, $address) : false;
                if (!$vatValidate) {
                    $this->writeln('<error>'.
                        __('Email %1 - Endereço inválido - %2', $email, $taxvat)
                        .'</error>');
                    continue;
                }
                $this->setFormatedPhone($address);
                try {
                    $this->addressFactory->save($address);
                    $vatId = $address->getVatId();
                    $phone = $address->getTelephone();
                    $this->writeln('<info>'.
                        __('Email %1 - VatId %2 - Phone %3', $email, $vatId, $phone).
                        '</info>');
                } catch (LocalizedException $exc) {
                    $this->addressFactory->deleteById($address->getId());
                    $msg = $exc->getMessage();
                    $this->writeln('<error>'.__('Email %1 - Erro %2', $email, $msg).'</error>');
                }
            }
        }
    }
    
    /**
     * Set Formated Phone.
     *
     * @param Address $address
     *
     * @return void
     */
    public function setFormatedPhone(
        Address $address
    ) {
        $phone = $address->getTelephone();
        $phone2 = $address->getFax();

        $phone = preg_replace('/[^0-9]/', '', (string)$phone);
        $phone2 = preg_replace('/[^0-9]/', '', (string)$phone2);

        if (strlen($phone) !== 11 && strlen($phone2) === 11) {
            $parts = sscanf($phone2, '%2c%5c%4c');
            $phone2 = "({$parts[0]}){$parts[1]}-{$parts[2]}";
            $address->setTelephone($phone2);
            $address->setFax($phone);
        }

        if (strlen($phone) === 11) {
            $parts = sscanf($phone, '%2c%5c%4c');
            $phone = "({$parts[0]}){$parts[1]}-{$parts[2]}";
            $address->setTelephone($phone);
        }
    }

    /**
     * Set Vat Id In Address.
     *
     * @param string   $taxvat
     * @param Address  $address
     *
     * @return bool
     */
    public function setVatIdInAddress(
        string $taxvat,
        Address $address
    ) {
        $taxvat = preg_replace('/[^0-9]/', '', $taxvat);
       
        if (strlen($taxvat) === 11) {
            $parts = sscanf($taxvat, '%3c%3c%3c%2c');
            $taxvat = "{$parts[0]}.{$parts[1]}.{$parts[2]}-{$parts[3]}";
            $address->setVatId($taxvat);
        } elseif (strlen($taxvat) === 14) {
            $parts = sscanf($taxvat, '%2c%3c%3c%4c%2c');
            $taxvat = "{$parts[0]}.{$parts[1]}.{$parts[2]}/{$parts[3]}-{$parts[4]}";
            $address->setVatId($taxvat);
        } elseif (strlen($taxvat) !== 14 && strlen($taxvat) !== 11) {
            try {
                $this->addressFactory->deleteById($address->getId());
            } catch (LocalizedException $exc) {
                return false;
            }
            return false;
        }
        return true;
    }

    /**
     * Set Default Address.
     *
     * @param CustomerInterceptor $customer
     *
     * @return bool
     */
    public function setDefaultAddress(
        CustomerInterceptor $customer
    ) {
        $customerId = $customer->getId();
        $searchCriteria = $this->searchCriteria->addFilter('parent_id', $customerId)->create();
        $addressRepository = $this->addressFactory->getList($searchCriteria);
        if (count($addressRepository->getItems()) > 0) {
            foreach ($addressRepository->getItems() as $address) {
                $customer->setDefaultBilling($address->getId());
                $customer->setDefaultShipping($address->getId());
                try {
                    $customer->save();
                    $email = $customer->getEmail();
                    $vatId = $address->getVatId();
                    $phone = $address->getTelephone();
                    $this->writeln('<info>'.
                        __('New Default Billing and Shipping Address for Email %1', $email).
                        '</info>');
                    $this->writeln('<info>'.
                        __('Email %1 - VatId %2 - Phone %3', $email, $vatId, $phone).
                        '</info>');
                } catch (LocalizedException $exc) {
                    $email = $customer->getEmail();
                    $msg = $exc->getMessage();
                    $this->writeln('<error>'.__('Email %1 - Erro %2', $email, $msg).'</error>');
                }
                continue;
            }
        }

        return true;
    }
}
