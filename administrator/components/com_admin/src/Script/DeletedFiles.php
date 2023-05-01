<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Script;

\defined('_JEXEC') or die;

/**
 * Deleted files registry for the script file of Joomla CMS
 *
 * @since  __DEPLOY_VERSION__
 */
class DeletedFiles
{
    /**
     * The list of files to be deleted on CMS update
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    public $files = [
        // From 4.4 to 5.0
        '/administrator/components/com_admin/sql/others/mysql/utf8mb4-conversion.sql',
        '/administrator/components/com_admin/sql/others/mysql/utf8mb4-conversion_optional.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2018-03-05.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2018-05-15.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2018-07-19.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2018-07-29.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2018-08-29.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-03-09.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-03-30.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-04-15.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-04-22.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-05-20.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-06-29.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-07-13.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-09-13.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-09-22.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-10-06.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-10-17.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-02-02.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-03-10.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-03-25.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-05-29.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-09-27.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-12-20.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-04-22.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-04-27.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-05-30.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-06-04.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-08-13.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-08-17.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.3-2021-09-04.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.3-2021-09-05.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.0.6-2021-12-23.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2021-11-20.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2021-11-28.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2021-12-29.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2022-01-08.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2022-01-19.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2022-01-24.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.1.1-2022-02-20.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.1.3-2022-04-07.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.1.3-2022-04-08.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.2.0-2022-05-15.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.2.0-2022-06-15.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.2.0-2022-06-19.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.2.0-2022-06-22.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.2.0-2022-07-07.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.2.1-2022-08-23.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.2.3-2022-09-07.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.2.7-2022-12-29.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.2.9-2023-03-07.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2022-09-23.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2022-11-06.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-01-30.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-02-15.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-02-25.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-03-07.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-03-09.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-03-10.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-03-28.sql',
        '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-03-29.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2018-03-05.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2018-05-15.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2018-07-19.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2018-07-29.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2018-08-29.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-03-09.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-03-30.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-04-15.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-04-22.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-05-20.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-06-29.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-07-13.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-09-13.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-09-22.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-10-06.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-10-17.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-02-02.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-03-10.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-03-25.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-05-29.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-08-01.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-09-27.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-12-20.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-04-22.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-04-27.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-05-30.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-06-04.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-08-13.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-08-17.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.3-2021-09-04.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.3-2021-09-05.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.0.6-2021-12-23.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2021-11-20.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2021-11-28.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2021-12-29.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2022-01-08.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2022-01-19.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2022-01-24.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.1.1-2022-02-20.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.1.3-2022-04-07.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.1.3-2022-04-08.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.2.0-2022-05-15.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.2.0-2022-06-19.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.2.0-2022-06-22.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.2.0-2022-07-07.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.2.1-2022-08-23.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.2.3-2022-09-07.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.2.7-2022-12-29.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.2.9-2023-03-07.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2022-09-23.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2022-11-06.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-01-30.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-02-15.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-02-25.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-03-07.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-03-09.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-03-10.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-03-28.sql',
        '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-03-29.sql',
        '/libraries/src/Schema/ChangeItem/SqlsrvChangeItem.php',
        '/libraries/vendor/beberlei/assert/LICENSE',
        '/libraries/vendor/beberlei/assert/lib/Assert/Assert.php',
        '/libraries/vendor/beberlei/assert/lib/Assert/Assertion.php',
        '/libraries/vendor/beberlei/assert/lib/Assert/AssertionChain.php',
        '/libraries/vendor/beberlei/assert/lib/Assert/AssertionFailedException.php',
        '/libraries/vendor/beberlei/assert/lib/Assert/InvalidArgumentException.php',
        '/libraries/vendor/beberlei/assert/lib/Assert/LazyAssertion.php',
        '/libraries/vendor/beberlei/assert/lib/Assert/LazyAssertionException.php',
        '/libraries/vendor/beberlei/assert/lib/Assert/functions.php',
        '/libraries/vendor/google/recaptcha/ARCHITECTURE.md',
        '/libraries/vendor/jfcherng/php-color-output/src/helpers.php',
        '/libraries/vendor/joomla/ldap/LICENSE',
        '/libraries/vendor/joomla/ldap/src/LdapClient.php',
        '/libraries/vendor/laminas/laminas-zendframework-bridge/COPYRIGHT.md',
        '/libraries/vendor/laminas/laminas-zendframework-bridge/LICENSE.md',
        '/libraries/vendor/laminas/laminas-zendframework-bridge/config/replacements.php',
        '/libraries/vendor/laminas/laminas-zendframework-bridge/src/Autoloader.php',
        '/libraries/vendor/laminas/laminas-zendframework-bridge/src/ConfigPostProcessor.php',
        '/libraries/vendor/laminas/laminas-zendframework-bridge/src/Module.php',
        '/libraries/vendor/laminas/laminas-zendframework-bridge/src/Replacements.php',
        '/libraries/vendor/laminas/laminas-zendframework-bridge/src/RewriteRules.php',
        '/libraries/vendor/laminas/laminas-zendframework-bridge/src/autoload.php',
        '/libraries/vendor/lcobucci/jwt/compat/class-aliases.php',
        '/libraries/vendor/lcobucci/jwt/compat/json-exception-polyfill.php',
        '/libraries/vendor/lcobucci/jwt/compat/lcobucci-clock-polyfill.php',
        '/libraries/vendor/lcobucci/jwt/src/Claim.php',
        '/libraries/vendor/lcobucci/jwt/src/Claim/Basic.php',
        '/libraries/vendor/lcobucci/jwt/src/Claim/EqualsTo.php',
        '/libraries/vendor/lcobucci/jwt/src/Claim/Factory.php',
        '/libraries/vendor/lcobucci/jwt/src/Claim/GreaterOrEqualsTo.php',
        '/libraries/vendor/lcobucci/jwt/src/Claim/LesserOrEqualsTo.php',
        '/libraries/vendor/lcobucci/jwt/src/Claim/Validatable.php',
        '/libraries/vendor/lcobucci/jwt/src/Parsing/Decoder.php',
        '/libraries/vendor/lcobucci/jwt/src/Parsing/Encoder.php',
        '/libraries/vendor/lcobucci/jwt/src/Signature.php',
        '/libraries/vendor/lcobucci/jwt/src/Signer/BaseSigner.php',
        '/libraries/vendor/lcobucci/jwt/src/Signer/Keychain.php',
        '/libraries/vendor/lcobucci/jwt/src/ValidationData.php',
        '/libraries/vendor/nyholm/psr7/LICENSE',
        '/libraries/vendor/nyholm/psr7/src/Factory/HttplugFactory.php',
        '/libraries/vendor/nyholm/psr7/src/Factory/Psr17Factory.php',
        '/libraries/vendor/nyholm/psr7/src/MessageTrait.php',
        '/libraries/vendor/nyholm/psr7/src/Request.php',
        '/libraries/vendor/nyholm/psr7/src/RequestTrait.php',
        '/libraries/vendor/nyholm/psr7/src/Response.php',
        '/libraries/vendor/nyholm/psr7/src/ServerRequest.php',
        '/libraries/vendor/nyholm/psr7/src/Stream.php',
        '/libraries/vendor/nyholm/psr7/src/UploadedFile.php',
        '/libraries/vendor/nyholm/psr7/src/Uri.php',
        '/libraries/vendor/php-http/message-factory/LICENSE',
        '/libraries/vendor/php-http/message-factory/puli.json',
        '/libraries/vendor/php-http/message-factory/src/MessageFactory.php',
        '/libraries/vendor/php-http/message-factory/src/RequestFactory.php',
        '/libraries/vendor/php-http/message-factory/src/ResponseFactory.php',
        '/libraries/vendor/php-http/message-factory/src/StreamFactory.php',
        '/libraries/vendor/php-http/message-factory/src/UriFactory.php',
        '/libraries/vendor/psr/log/Psr/Log/AbstractLogger.php',
        '/libraries/vendor/psr/log/Psr/Log/InvalidArgumentException.php',
        '/libraries/vendor/psr/log/Psr/Log/LogLevel.php',
        '/libraries/vendor/psr/log/Psr/Log/LoggerAwareInterface.php',
        '/libraries/vendor/psr/log/Psr/Log/LoggerAwareTrait.php',
        '/libraries/vendor/psr/log/Psr/Log/LoggerInterface.php',
        '/libraries/vendor/psr/log/Psr/Log/LoggerTrait.php',
        '/libraries/vendor/psr/log/Psr/Log/NullLogger.php',
        '/libraries/vendor/ramsey/uuid/LICENSE',
        '/libraries/vendor/ramsey/uuid/src/BinaryUtils.php',
        '/libraries/vendor/ramsey/uuid/src/Builder/DefaultUuidBuilder.php',
        '/libraries/vendor/ramsey/uuid/src/Builder/DegradedUuidBuilder.php',
        '/libraries/vendor/ramsey/uuid/src/Builder/UuidBuilderInterface.php',
        '/libraries/vendor/ramsey/uuid/src/Codec/CodecInterface.php',
        '/libraries/vendor/ramsey/uuid/src/Codec/GuidStringCodec.php',
        '/libraries/vendor/ramsey/uuid/src/Codec/OrderedTimeCodec.php',
        '/libraries/vendor/ramsey/uuid/src/Codec/StringCodec.php',
        '/libraries/vendor/ramsey/uuid/src/Codec/TimestampFirstCombCodec.php',
        '/libraries/vendor/ramsey/uuid/src/Codec/TimestampLastCombCodec.php',
        '/libraries/vendor/ramsey/uuid/src/Converter/Number/BigNumberConverter.php',
        '/libraries/vendor/ramsey/uuid/src/Converter/Number/DegradedNumberConverter.php',
        '/libraries/vendor/ramsey/uuid/src/Converter/NumberConverterInterface.php',
        '/libraries/vendor/ramsey/uuid/src/Converter/Time/BigNumberTimeConverter.php',
        '/libraries/vendor/ramsey/uuid/src/Converter/Time/DegradedTimeConverter.php',
        '/libraries/vendor/ramsey/uuid/src/Converter/Time/PhpTimeConverter.php',
        '/libraries/vendor/ramsey/uuid/src/Converter/TimeConverterInterface.php',
        '/libraries/vendor/ramsey/uuid/src/DegradedUuid.php',
        '/libraries/vendor/ramsey/uuid/src/Exception/InvalidUuidStringException.php',
        '/libraries/vendor/ramsey/uuid/src/Exception/UnsatisfiedDependencyException.php',
        '/libraries/vendor/ramsey/uuid/src/Exception/UnsupportedOperationException.php',
        '/libraries/vendor/ramsey/uuid/src/FeatureSet.php',
        '/libraries/vendor/ramsey/uuid/src/Generator/CombGenerator.php',
        '/libraries/vendor/ramsey/uuid/src/Generator/DefaultTimeGenerator.php',
        '/libraries/vendor/ramsey/uuid/src/Generator/MtRandGenerator.php',
        '/libraries/vendor/ramsey/uuid/src/Generator/OpenSslGenerator.php',
        '/libraries/vendor/ramsey/uuid/src/Generator/PeclUuidRandomGenerator.php',
        '/libraries/vendor/ramsey/uuid/src/Generator/PeclUuidTimeGenerator.php',
        '/libraries/vendor/ramsey/uuid/src/Generator/RandomBytesGenerator.php',
        '/libraries/vendor/ramsey/uuid/src/Generator/RandomGeneratorFactory.php',
        '/libraries/vendor/ramsey/uuid/src/Generator/RandomGeneratorInterface.php',
        '/libraries/vendor/ramsey/uuid/src/Generator/RandomLibAdapter.php',
        '/libraries/vendor/ramsey/uuid/src/Generator/SodiumRandomGenerator.php',
        '/libraries/vendor/ramsey/uuid/src/Generator/TimeGeneratorFactory.php',
        '/libraries/vendor/ramsey/uuid/src/Generator/TimeGeneratorInterface.php',
        '/libraries/vendor/ramsey/uuid/src/Provider/Node/FallbackNodeProvider.php',
        '/libraries/vendor/ramsey/uuid/src/Provider/Node/RandomNodeProvider.php',
        '/libraries/vendor/ramsey/uuid/src/Provider/Node/SystemNodeProvider.php',
        '/libraries/vendor/ramsey/uuid/src/Provider/NodeProviderInterface.php',
        '/libraries/vendor/ramsey/uuid/src/Provider/Time/FixedTimeProvider.php',
        '/libraries/vendor/ramsey/uuid/src/Provider/Time/SystemTimeProvider.php',
        '/libraries/vendor/ramsey/uuid/src/Provider/TimeProviderInterface.php',
        '/libraries/vendor/ramsey/uuid/src/Uuid.php',
        '/libraries/vendor/ramsey/uuid/src/UuidFactory.php',
        '/libraries/vendor/ramsey/uuid/src/UuidFactoryInterface.php',
        '/libraries/vendor/ramsey/uuid/src/UuidInterface.php',
        '/libraries/vendor/ramsey/uuid/src/functions.php',
        '/libraries/vendor/spomky-labs/base64url/LICENSE',
        '/libraries/vendor/spomky-labs/base64url/src/Base64Url.php',
        '/libraries/vendor/spomky-labs/cbor-php/src/ByteStringWithChunkObject.php',
        '/libraries/vendor/spomky-labs/cbor-php/src/InfiniteListObject.php',
        '/libraries/vendor/spomky-labs/cbor-php/src/InfiniteMapObject.php',
        '/libraries/vendor/spomky-labs/cbor-php/src/SignedIntegerObject.php',
        '/libraries/vendor/spomky-labs/cbor-php/src/Tag/EpochTag.php',
        '/libraries/vendor/spomky-labs/cbor-php/src/Tag/PositiveBigIntegerTag.php',
        '/libraries/vendor/spomky-labs/cbor-php/src/Tag/TagObjectManager.php',
        '/libraries/vendor/spomky-labs/cbor-php/src/TagObject.php',
        '/libraries/vendor/spomky-labs/cbor-php/src/TextStringWithChunkObject.php',
        '/libraries/vendor/symfony/polyfill-php72/bootstrap.php',
        '/libraries/vendor/symfony/polyfill-php72/LICENSE',
        '/libraries/vendor/symfony/polyfill-php72/Php72.php',
        '/libraries/vendor/symfony/polyfill-php73/bootstrap.php',
        '/libraries/vendor/symfony/polyfill-php73/LICENSE',
        '/libraries/vendor/symfony/polyfill-php73/Php73.php',
        '/libraries/vendor/symfony/polyfill-php73/Resources/stubs/JsonException.php',
        '/libraries/vendor/symfony/polyfill-php80/bootstrap.php',
        '/libraries/vendor/symfony/polyfill-php80/LICENSE',
        '/libraries/vendor/symfony/polyfill-php80/Php80.php',
        '/libraries/vendor/symfony/polyfill-php80/PhpToken.php',
        '/libraries/vendor/symfony/polyfill-php80/Resources/stubs/Attribute.php',
        '/libraries/vendor/symfony/polyfill-php80/Resources/stubs/PhpToken.php',
        '/libraries/vendor/symfony/polyfill-php80/Resources/stubs/Stringable.php',
        '/libraries/vendor/symfony/polyfill-php80/Resources/stubs/UnhandledMatchError.php',
        '/libraries/vendor/symfony/polyfill-php80/Resources/stubs/ValueError.php',
        '/libraries/vendor/symfony/polyfill-php81/bootstrap.php',
        '/libraries/vendor/symfony/polyfill-php81/LICENSE',
        '/libraries/vendor/symfony/polyfill-php81/Php81.php',
        '/libraries/vendor/symfony/polyfill-php81/Resources/stubs/ReturnTypeWillChange.php',
        '/libraries/vendor/web-auth/cose-lib/src/Verifier.php',
        '/libraries/vendor/web-auth/metadata-service/src/AuthenticatorStatus.php',
        '/libraries/vendor/web-auth/metadata-service/src/BiometricAccuracyDescriptor.php',
        '/libraries/vendor/web-auth/metadata-service/src/BiometricStatusReport.php',
        '/libraries/vendor/web-auth/metadata-service/src/CodeAccuracyDescriptor.php',
        '/libraries/vendor/web-auth/metadata-service/src/DisplayPNGCharacteristicsDescriptor.php',
        '/libraries/vendor/web-auth/metadata-service/src/DistantSingleMetadata.php',
        '/libraries/vendor/web-auth/metadata-service/src/DistantSingleMetadataFactory.php',
        '/libraries/vendor/web-auth/metadata-service/src/EcdaaTrustAnchor.php',
        '/libraries/vendor/web-auth/metadata-service/src/ExtensionDescriptor.php',
        '/libraries/vendor/web-auth/metadata-service/src/MetadataService.php',
        '/libraries/vendor/web-auth/metadata-service/src/MetadataServiceFactory.php',
        '/libraries/vendor/web-auth/metadata-service/src/MetadataStatement.php',
        '/libraries/vendor/web-auth/metadata-service/src/MetadataStatementFetcher.php',
        '/libraries/vendor/web-auth/metadata-service/src/MetadataTOCPayload.php',
        '/libraries/vendor/web-auth/metadata-service/src/MetadataTOCPayloadEntry.php',
        '/libraries/vendor/web-auth/metadata-service/src/PatternAccuracyDescriptor.php',
        '/libraries/vendor/web-auth/metadata-service/src/RgbPaletteEntry.php',
        '/libraries/vendor/web-auth/metadata-service/src/RogueListEntry.php',
        '/libraries/vendor/web-auth/metadata-service/src/SimpleMetadataStatementRepository.php',
        '/libraries/vendor/web-auth/metadata-service/src/SingleMetadata.php',
        '/libraries/vendor/web-auth/metadata-service/src/StatusReport.php',
        '/libraries/vendor/web-auth/metadata-service/src/VerificationMethodANDCombinations.php',
        '/libraries/vendor/web-auth/metadata-service/src/VerificationMethodDescriptor.php',
        '/libraries/vendor/web-auth/metadata-service/src/Version.php',
        '/libraries/vendor/web-auth/webauthn-lib/src/Server.php',
        '/libraries/vendor/web-token/jwt-signature-algorithm-rsa/RSA.php',
        '/media/vendor/tinymce/plugins/bbcode/index.js',
        '/media/vendor/tinymce/plugins/bbcode/plugin.js',
        '/media/vendor/tinymce/plugins/bbcode/plugin.min.js',
        '/media/vendor/tinymce/plugins/bbcode/plugin.min.js.gz',
        '/media/vendor/tinymce/plugins/colorpicker/index.js',
        '/media/vendor/tinymce/plugins/colorpicker/plugin.js',
        '/media/vendor/tinymce/plugins/colorpicker/plugin.min.js',
        '/media/vendor/tinymce/plugins/colorpicker/plugin.min.js.gz',
        '/media/vendor/tinymce/plugins/contextmenu/index.js',
        '/media/vendor/tinymce/plugins/contextmenu/plugin.js',
        '/media/vendor/tinymce/plugins/contextmenu/plugin.min.js',
        '/media/vendor/tinymce/plugins/contextmenu/plugin.min.js.gz',
        '/media/vendor/tinymce/plugins/fullpage/index.js',
        '/media/vendor/tinymce/plugins/fullpage/plugin.js',
        '/media/vendor/tinymce/plugins/fullpage/plugin.min.js',
        '/media/vendor/tinymce/plugins/fullpage/plugin.min.js.gz',
        '/media/vendor/tinymce/plugins/hr/index.js',
        '/media/vendor/tinymce/plugins/hr/plugin.js',
        '/media/vendor/tinymce/plugins/hr/plugin.min.js',
        '/media/vendor/tinymce/plugins/hr/plugin.min.js.gz',
        '/media/vendor/tinymce/plugins/imagetools/index.js',
        '/media/vendor/tinymce/plugins/imagetools/plugin.js',
        '/media/vendor/tinymce/plugins/imagetools/plugin.min.js',
        '/media/vendor/tinymce/plugins/imagetools/plugin.min.js.gz',
        '/media/vendor/tinymce/plugins/legacyoutput/index.js',
        '/media/vendor/tinymce/plugins/legacyoutput/plugin.js',
        '/media/vendor/tinymce/plugins/legacyoutput/plugin.min.js',
        '/media/vendor/tinymce/plugins/legacyoutput/plugin.min.js.gz',
        '/media/vendor/tinymce/plugins/noneditable/index.js',
        '/media/vendor/tinymce/plugins/noneditable/plugin.js',
        '/media/vendor/tinymce/plugins/noneditable/plugin.min.js',
        '/media/vendor/tinymce/plugins/noneditable/plugin.min.js.gz',
        '/media/vendor/tinymce/plugins/paste/index.js',
        '/media/vendor/tinymce/plugins/paste/plugin.js',
        '/media/vendor/tinymce/plugins/paste/plugin.min.js',
        '/media/vendor/tinymce/plugins/paste/plugin.min.js.gz',
        '/media/vendor/tinymce/plugins/print/index.js',
        '/media/vendor/tinymce/plugins/print/plugin.js',
        '/media/vendor/tinymce/plugins/print/plugin.min.js',
        '/media/vendor/tinymce/plugins/print/plugin.min.js.gz',
        '/media/vendor/tinymce/plugins/spellchecker/index.js',
        '/media/vendor/tinymce/plugins/spellchecker/plugin.js',
        '/media/vendor/tinymce/plugins/spellchecker/plugin.min.js',
        '/media/vendor/tinymce/plugins/spellchecker/plugin.min.js.gz',
        '/media/vendor/tinymce/plugins/tabfocus/index.js',
        '/media/vendor/tinymce/plugins/tabfocus/plugin.js',
        '/media/vendor/tinymce/plugins/tabfocus/plugin.min.js',
        '/media/vendor/tinymce/plugins/tabfocus/plugin.min.js.gz',
        '/media/vendor/tinymce/plugins/textcolor/index.js',
        '/media/vendor/tinymce/plugins/textcolor/plugin.js',
        '/media/vendor/tinymce/plugins/textcolor/plugin.min.js',
        '/media/vendor/tinymce/plugins/textcolor/plugin.min.js.gz',
        '/media/vendor/tinymce/plugins/textpattern/index.js',
        '/media/vendor/tinymce/plugins/textpattern/plugin.js',
        '/media/vendor/tinymce/plugins/textpattern/plugin.min.js',
        '/media/vendor/tinymce/plugins/textpattern/plugin.min.js.gz',
        '/media/vendor/tinymce/plugins/toc/index.js',
        '/media/vendor/tinymce/plugins/toc/plugin.js',
        '/media/vendor/tinymce/plugins/toc/plugin.min.js',
        '/media/vendor/tinymce/plugins/toc/plugin.min.js.gz',
        '/media/vendor/tinymce/skins/ui/oxide-dark/content.mobile.css',
        '/media/vendor/tinymce/skins/ui/oxide-dark/content.mobile.min.css',
        '/media/vendor/tinymce/skins/ui/oxide-dark/content.mobile.min.css.gz',
        '/media/vendor/tinymce/skins/ui/oxide-dark/fonts/tinymce-mobile.woff',
        '/media/vendor/tinymce/skins/ui/oxide-dark/skin.mobile.css',
        '/media/vendor/tinymce/skins/ui/oxide-dark/skin.mobile.min.css',
        '/media/vendor/tinymce/skins/ui/oxide-dark/skin.mobile.min.css.gz',
        '/media/vendor/tinymce/skins/ui/oxide/content.mobile.css',
        '/media/vendor/tinymce/skins/ui/oxide/content.mobile.min.css',
        '/media/vendor/tinymce/skins/ui/oxide/content.mobile.min.css.gz',
        '/media/vendor/tinymce/skins/ui/oxide/fonts/tinymce-mobile.woff',
        '/media/vendor/tinymce/skins/ui/oxide/skin.mobile.css',
        '/media/vendor/tinymce/skins/ui/oxide/skin.mobile.min.css',
        '/media/vendor/tinymce/skins/ui/oxide/skin.mobile.min.css.gz',
        '/media/vendor/tinymce/themes/mobile/index.js',
        '/media/vendor/tinymce/themes/mobile/theme.js',
        '/media/vendor/tinymce/themes/mobile/theme.min.js',
        '/media/vendor/tinymce/themes/mobile/theme.min.js.gz',
        '/plugins/multifactorauth/webauthn/src/Hotfix/AndroidKeyAttestationStatementSupport.php',
        '/plugins/multifactorauth/webauthn/src/Hotfix/FidoU2FAttestationStatementSupport.php',
        '/plugins/multifactorauth/webauthn/src/Hotfix/Server.php',
        '/plugins/system/webauthn/src/Hotfix/AndroidKeyAttestationStatementSupport.php',
        '/plugins/system/webauthn/src/Hotfix/FidoU2FAttestationStatementSupport.php',
        '/plugins/system/webauthn/src/Hotfix/Server.php',
    ];
}
