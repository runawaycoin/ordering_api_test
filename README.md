# Order API Test App #

# Task #
![](BackendTask.png)

# Approach #
I created a new Symfony 6 (framework) web app

Utilising Symfony Components: Validator, Form, Doctrine, Migrations, Serializer, Console

Using PHP8 attributes

No front end, all API back end, tested using swagger front end and unit tests 

# Database Design #
![](orderingApiTest_diagram.png)

# API #
Swagger API doc

Api is split for admins and users, using Symfony security firewall (/v1 and /v1/admin root paths)

![](api.png)

Create new order example

example json request: 
```
{"deliveryAddress" : 1,"billingAddress" : 1,"items":[{"id":1,"quantity":2}]}
```
![](order-submit.jpg)

# Command #
Process delayed orders command see: [OrderControllerTest](src/Command/ProcessDelayedOrdersCommand.php)

# Data Validation #
Uses Validation component and forms to ensure valid input

Eg price is above 0, item name is not blank.

See entity attributes

![](validation-entity.jpg)

See error response over api

![](validation-swagger.jpg)


# Unit Tests #
Full unit tested api

see: [Controller tests](tests/Controller)