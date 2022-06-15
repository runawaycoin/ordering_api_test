Order API Test App

# Task #
![](BackendTask.png)

# Database Design #
![](orderingApiTest_diagram.png)

# API #
Swagger API doc

Api is split for admins and users

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
Full unit test for api
see: [OrderControllerTest](tests/OrderControllerTest.php)