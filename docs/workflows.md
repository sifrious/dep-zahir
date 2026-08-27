# Workflows

## Sign in and resolve an account

1. A product redirects a person to the selected identity provider.
2. The provider authenticates the person and returns a signed identity assertion.
3. The product or Accounts validates the assertion under the accepted protocol.
4. Accounts resolves the assertion's issuer and subject to one external identity.
5. Accounts returns the linked stable Account, creating it only under the accepted account-creation policy.
6. The product stores the Account ID in its local projection.

The product invokes this workflow through the Accounts client package. Its selected `LoginDriver` performs the provider redirect and converts the verified callback into the provider-independent issuer and subject value.

## Check product access

1. A product authenticates itself to Accounts.
2. The product supplies an Account ID and entitlement key.
3. Accounts evaluates active grants for that account and product.
4. Accounts returns an explicit allowed or denied decision with the entitlement and evaluation time.

## Apply a future payment event

1. The payment provider sends a signed webhook.
2. Accounts verifies the provider signature and idempotency identifier.
3. Accounts maps the external customer and commercial state to a stable Account.
4. Accounts records the provider event without storing payment instruments.
5. Accounts adds, changes, or revokes named entitlements according to accepted product policy.
6. Connected products observe the new entitlement through API reads or future events.

## Publish a legal or compliance document

1. The responsible owner supplies approved document content, version, and effective date.
2. The document entry is updated without changing its stable public slug.
3. The publication flag is enabled only for the approved version.
4. The trust center links the published document and exposes its metadata.
5. Connected products link to the Accounts URL instead of copying the content.
