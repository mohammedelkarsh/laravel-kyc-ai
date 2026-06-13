# Test coverage

```bash
composer install
composer test
composer test:comprehensive   # human-readable testdox output
```

## Test suites

| Area | Test class |
|------|------------|
| Levels | `KycLevelTest` |
| Internal verify (SA/AE/EG) | `InternalVerificationScenariosTest` |
| Extraction drivers | `ExtractionDriversTest` |
| External (registry) | `ExternalDriverRegistryTest`, `ExternalVerificationTest`, `StandardExternalDriverTest` |
| Confidence & privacy | `ConfidenceAndWarningsTest` |
| Events | `KycEventsTest` |
| Audit DB | `KycAuditTest` |
| HTTP (API + demo) | `KycHttpTest` |
| Validation rule | `KycDocumentRuleTest` |
| Result DTO | `KycResultTest`, `ExtractedDocumentTest` |
| Request / job | `KycRequestDataTest`, `ProcessKycDocumentJobTest` |
| Facade & queue | `KycFacadeAndDispatchTest` |
| Package bindings | `PackageBindingsTest` |
| Document input | `DocumentSourceTest` |
| OCR parsing | `NationalIdExtractorTest` |
| Manager | `KycManagerTest` |
| Null external | `NullExternalVerifierTest` |
| E2E matrix | `ComprehensiveKycScenariosTest` |

## Local monorepo development

If you develop alongside the `validators` monorepo, copy the example local Composer file:

```bash
cp composer.local.json.example composer.local.json
composer update
```

`composer.local.json` is gitignored and not published to Packagist consumers.
