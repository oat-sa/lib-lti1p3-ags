# AGS scopes permissions voter

> How to use the [ScopePermissionVoter](../../src/Voter/ScopePermissionVoter.php) to check what is allowed to do as a tool from the AGS claim given by the platform at LTI launch time.

During an LTI launch, the platform may provide an [AGS claim](https://www.imsglobal.org/spec/lti-ags/v2p0#example-service-claims) containing the list of allowed scopes:

```json
"https://purl.imsglobal.org/spec/lti-ags/claim/endpoint": {
  "scope": [
    "https://purl.imsglobal.org/spec/lti-ags/scope/lineitem.readonly",
    "https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly",
    "https://purl.imsglobal.org/spec/lti-ags/scope/score"
  ],
  "lineitems": "https://www.myuniv.example.com/2344/lineitems/",
  "lineitem": "https://www.myuniv.example.com/2344/lineitems/1234/lineitem"
}
```

You can use the [ScopePermissionVoter](../../src/Voter/ScopePermissionVoter.php) to easily check what is allowed by the platform:

```php
<?php

use OAT\Library\Lti1p3Ags\Voter\ScopePermissionVoter;

$agsClaimScopes = [
    "https://purl.imsglobal.org/spec/lti-ags/scope/lineitem.readonly",
    "https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly",
];

var_dump(ScopePermissionVoter::canReadLineItem($agsClaimScopes));  // true
var_dump(ScopePermissionVoter::canWriteLineItem($agsClaimScopes)); // false
var_dump(ScopePermissionVoter::canReadResult($agsClaimScopes));    // true
var_dump(ScopePermissionVoter::canWriteScore($agsClaimScopes));    // false

// You can also get all permissions at once
var_dump(ScopePermissionVoter::getPermissions($agsClaimScopes));
```
