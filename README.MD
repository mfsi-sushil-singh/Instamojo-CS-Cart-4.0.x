Instamojo-CSCart for CS-Cart and Multi-Vendor versions 4.0.x
====
----
This module allows us to use [Instamojo](https://www.instamojo.com) as Payment Gateway in CS-Cart and Multi-Vendor 4.0.x.

### Installation
---
- Download the [zip file](https://github.com/ashwch/Instamojo-CS-Cart-4.0.x/archive/master.zip) and paste the content of **upload** folder into your sites main directory, this is **public_html** is most cases. Note that inside **public_html** you will already have folders named **app** and **design**, so you are supposed to merge the **app** and **design** from our module to those folders.


### Logs
---
Before we start with the setup I would like you to create a log file to keep track of things, this file will help us in finding issues with the transactions as well as plugin. So, this is a **must do** thing.

CS-Cart logs everything to **"var/log"** directory, if you don't have a **log** directory under **var** then create one and give it enough permissions so that the plugin can create a file named **"imojo.log"** under it and write logs to it.

While sharing issues related to plugin do share your **"imojo.log"** file as well. Our support email id is: support@instamojo.com

### Add Instamojo to database
---

- Open the **sql_install.txt** file and copy its content.  Now open phpMyAdmin or which ever MySQL client you are using and run the copied MySQL query. This will add an entry of **Instamojo** in **cscart_payment_processors** table.

![enter image description here](http://i.imgur.com/Q4dhIHR.png)

### Add a new Payment method
---

Now it is time to create a new Payment Gateway backend from Admin backend.

- In Admin backend go to **Adminstration -> Payment methods**.

![enter image description here](http://i.imgur.com/8fXZ1LU.png)

- Now click on the **+** button the top-right side to add a new Payment method, this will open a form.

![enter image description here](http://i.imgur.com/tHwWWjY.png)

- In **Name** field type **"Instamojo"**.
- In **Processor** dropdown select **Instamojo**(It's under **Gateways** section).
- Leave Template as is . 
- In **Payment category** select **Credit card**.
- In Description you can add some text that will be displayed to users during checkout, for example: **"Pay using DB/CC or Online Banking"**.
- Make sure the **Status** is set to **Active**.
- In **Icon** you can choose the file **icon.png** received with the zip file.

Currently your form will look like:

![enter image description here](http://i.imgur.com/QRmS2dC.png)

Now click on **Create** to create the Payment method.

### Configuration
---

Click back on **Instamojo** payment method we have just created inside  **Adminstration -> Payment methods** and then click on **Configure**, in some cases due to caching it shows an empty page.

![enter image description here](http://i.imgur.com/bczghsC.png)

To fix this you will have to clean up the backend cache from **"var/cache/templates/backend/"**, clean up all the files(**.php** files) from this folder and refresh the Payment method page. 

Note that if this form was already there then there's no need to clean the cache.

Here we now have **six** fields, in the coming sections I will explain from where to get the values for these fields.

![enter image description here](http://i.imgur.com/5HRB3IU.png)


### Creating a Payment Link
----
In this section we will learn how to create a Payment link along with how to get the required values for `Payment Link` and `Custom Field` .

- Create a Payment Link on Instamojo under the **Services/Membership** option.

  Set the price to Rs. 10 and enable **"Pay what you want"**.  Under **Title** and **Description**, you may enter something that describes your business and the nature of the products being sold.

  Under **Advanced settings** of the same link there's a field **Custom Redirection URL**, here if your website's URL is **http://www.example.com** then use **http://www.example.com/index.php?dispatch=payment_notification.process&payment=instamojo** as **Custom Redirection URL**. This is **important** because it will be used to register the Payment.

![enter image description here](http://i.imgur.com/Sq2XW0d.png)

 Now click on **Get started** to save the button.
 
- Now copy the Payment Link URL and paste this in **Payment URL** field on the Instamojo module configuration page. URL's format is usually: **https://www.instamojo.com/username/slug/**.
- Now on the Payment Link page go to **More options** and click on **Custom Fields**
 Create a custom field called **Order ID**, mark it as **required** and save it. In the custom field creation page, hover over the field you just created. You'll see a field with the format **Field_**. Note down the full name (including the **Field_** bit. Note that this is case sensitive!).

![enter image description here](http://i.imgur.com/tpdrj76.png)

Enter this name in the **Custom field** field on the Instamojo module configuration page.

### Auth
---
In this section we will learn how to get the values of fields  `API Key`,  `Auth token` and `Private salt`.

Go the [Instamojo developers](https://www.instamojo.com/developers/) page, if your are not logged in already then login first and then you'll see the value of `API Key`,  `Auth token`,  `Private salt` there on the bottom left side of the page.

Simply copy and paste their values in the configuration form in their respective fields.

### Currency Code

Now this is the only field we are left with. In this field you are supposed to store the Currency code of currency you're using right now, i.e Indian Rupee or something. If you don't have any such currency in backend then please create one by going to **Administration -> Currencies**.

![enter image description here](http://i.imgur.com/2rLSB0h.png)


For example here I have created a Currency named Indian Rupee with Code **"INR"**, so I am going to use **"INR"** in the Instamojo configuration form.

![enter image description here](http://i.imgur.com/lgfUdQ0.png) 

Now the form will look like:

![enter image description here](http://i.imgur.com/fOoraIk.png)

Click on **Save** to save these settings and we are good to go.

---

Now during **checkout** you will get an option of Instamojo.

![enter image description here](http://i.imgur.com/hS99NfF.png)
