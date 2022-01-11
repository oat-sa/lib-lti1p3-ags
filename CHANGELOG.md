CHANGELOG
=========

1.4.0
-----

* Added `LineItemContainerSerializer` and `ResultContainerSerializer`
* Added `JsonSerializer` with error handling for `json_encode` and `json_decode`
* Updated `LineItemContainerInterface` and `ResultContainerInterface` by extending `JsonSerializable`.

1.3.0
-----

* Added `milliseconds` to timestamp in `Score` JSON output

1.2.0
-----

* Added [submission review service](https://www.imsglobal.org/spec/lti-sr/v1p0) support (line item submission review)

1.1.1
-----

* Fixed models serialization to allow zero values

1.1.0
-----

* Added methods to line item, score and result clients to allow working from AGS claim or LTI message payload
* Updated documentation

1.0.0
-----

* Added models, factories, serializers for line items, scores and results
* Added LTI clients and LTI service request handlers for line items, scores and results
* Added scope permission voter and URL helpers
* Moved principal branch to main (former master)
* Upgraded for oat-sa/lib-lti1p3-core version 6.x
* Updated documentation

0.6.1
-----
* Fixed buildEndpointUrl of ScoreServiceClient when trimming line item URL provided by AGS claims

0.6.0
-----
* Upgraded for oat-sa/lib-lti1p3-core version 6.0.0

0.5.0
-----

* Upgraded for oat-sa/lib-lti1p3-core version 5.0.0

0.4.0
-----

* Upgraded for oat-sa/lib-lti1p3-core version 4.0.0
* Updated documentation

0.3.0
-----

* Upgraded for oat-sa/lib-lti1p3-core version 3.0.0
* Updated documentation

0.2.1
-----

* Updated /scores url generation to avoid parameters bug

0.2.0
-----

* Added ScoreFactory
* Updated Score id attribute to identifier

0.1.2
-----

* Fixed ScoreServiceClient requests content type

0.1.1
-----

* Fixed ScoreServiceClient


0.1.0
-----

* Added Score and LineItem models
* Added ScoreServiceClient (Tool side service)
