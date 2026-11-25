HTTPS and the Padlock Explained for Any Website
https-and-the-padlock-explained
If your browser says Not secure next to your address, some visitors leave. Here is what HTTPS is and how to check whether your site has it.
---
Look at your website address in the browser. If there is a padlock or a settings icon next to it, the connection is secure, which is called **HTTPS**. If it says "Not secure", the browser warns every visitor that something is wrong.

## What HTTPS does

It encrypts the connection between the visitor and the site. When someone fills in your form with a name and a phone number, nobody along the way, for example on public Wi-Fi, can read the data. HTTPS also guarantees that the person is really on your site and not on a swapped page. The certificate is like an identity card of the site, issued for one specific domain.

It is important to know what it does **not** do. HTTPS does not mean the site is safe from hackers or that the company behind it is honest. It protects only the connection, which is why scam sites today almost always have the padlock.

## Why it matters even without an online shop

Trust is the first reason. A "Not secure" warning scares people, especially right before they send an enquiry. Google also uses HTTPS as one of the signals when ranking results. Modern browsers increasingly restrict sites without HTTPS, warning people when they fill in a form and blocking some features such as location or camera. And if you collect personal data such as a name, phone or email, GDPR requires you to protect it, so an unprotected connection is hard to justify.

## How to check

Open your site with http at the front and no "s". It should automatically send you to https. Check that the padlock is there on every page and not only the home page. Click the padlock and see until when the certificate is valid. Then try the address with www in front and without it, because both should lead to the same secure address.

## How much it costs

In most cases nothing. Let's Encrypt certificates are free and renew automatically, and good hosting includes them by default. If someone asks you for money every year just for "SSL" on an ordinary company site, ask why. Paid certificates make sense only in special cases, for example for large companies or banks that want an extra check of the company. For a small business site, the free certificate protects the connection just as well.

## The most common problems

An expired certificate is the first. The certificate was set up, but automatic renewal stopped, and one day the site shows a frightening red warning. That is why monitoring should watch the expiry date as well.

Mixed content is the second. The site is on HTTPS, but a picture, script or font still loads over http. The browser then hides the padlock or shows a warning sign. It usually happens on old sites that moved to HTTPS later, and it is fixed by changing the addresses inside the site to https.

A certificate for the wrong address is the third. It may cover the www version but not the version without it, so visitors who type the address without www see a warning. The certificate must cover both.

A redirect that does not work is the fourth. The site opens on both http and https without sending people from one to the other. Google may then see two sites with the same content, and some visitors stay on the unprotected version.

## What to do if you do not have HTTPS

First look in the hosting control panel. There is often a button called Let's Encrypt or SSL, which turns it on with one click. After that, set up the redirect from http to https. Then change the addresses inside the site to https, and on WordPress there are plugins that do it. Finally update the address in Google Search Console and your Google profile if they still use http. If your hosting does not support free certificates, that is a good reason to think about changing it.

## Common questions

**Do I need HTTPS if my site has no form?** Yes. Browsers warn about every site without HTTPS, with or without a form, and Google takes it into account.

**Will I lose my Google positions when I move to HTTPS?** Not if the redirect is done correctly. Google expects it and recognises it.

The [free website check](/en/website-check) shows whether HTTPS works and whether the address without the "s" redirects properly. If you want someone to watch the certificate and the site all the time, see [maintenance and hosting](/en/services/maintenance-and-hosting).
