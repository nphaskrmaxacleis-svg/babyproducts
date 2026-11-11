<?php
interface PaymentSlipVerificationProvider {
    /** Real adapters must authenticate to their provider and verify transaction reference, amount and recipient. */
    public function verify(string $privateFile,array $order): array;
}
function configured_slip_verifier(): ?PaymentSlipVerificationProvider {return null;}
