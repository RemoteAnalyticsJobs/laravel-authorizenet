<?php

use ANet\Test\BaseTestCase;
use ANet\Traits\ANetPayments;


class ANetTest extends BaseTestCase
{
    /** @test */
    public function it_will_test_if_trait_implemented_class_returns_instance_of_anet_class()
    {
        $user = $this->getFakeUser();
        $this->assertInstanceOf('ANet\ANet', $user->anet());
    }

    /** @test */
    public function it_will_test_if_customer_profile_can_be_created()
    {
        $user = $this->getFakeUser();
        $customerProfile = $user->anet()->createCustomerProfile();
        $this->assertNotNull($customerProfile->getCustomerProfileId());
        $this->assertDatabaseHas('user_gateway_profiles', [
            'user_id' => $user->id,
            'profile_id' => $customerProfile->getCustomerProfileId()
        ]);
    }

    /** @test */
    public function it_will_Test_If_get_Customer_Id_method_returns_customer_id_of_the_user()
    {
        $user = $this->generateCustomerId();
        $gatewayProfile = \DB::table('user_gateway_profiles')->where('user_id', $user->id)->first();

        $this->assertEquals($gatewayProfile->profile_id, $user->anet()->getCustomerProfileId());
    }

    /** @test */
    public function it_will_test_If_payment_profile_can_be_Created_with_opache_data()
    {
        $user = $this->generateCustomerId();

        $opaqueData = $this->getOpaqueData();

        $paymentProfile = $user->anet()->createPaymentProfile([
            'dataValue' => $opaqueData->dataValue,
            'dataDescriptor' => $opaqueData->dataDescriptor
        ]);

        $this->assertNotNull($paymentProfile->getCustomerProfileId());
        $this->assertNotNull($paymentProfile->getCustomerPaymentProfileId());

        $this->assertDatabaseHas('user_payment_profiles', [
            'user_id' => $user->id,
            'payment_profile_id' =>$paymentProfile->getCustomerPaymentProfileId()
        ]);
    }

    /** @test */
    public function it_will_return_all_payment_profiles_for_a_user()
    {
        $user = $this->getCustomerWithPaymentProfile();

        $this->assertCount(1, $user->anet()->getPaymentProfiles());
    }

    /** @test */
    public function it_will_charge_the_user()
    {
        $user = $this->getCustomerWithPaymentProfile();
        $charge = $user->anet()->charge(1200, $user->anet()->getPaymentProfiles()[0]);
        $this->assertInstanceOf(net\authorize\api\contract\v1\CreateTransactionResponse::class, $charge);
    }

}
