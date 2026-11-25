Website Speed Test and How Fast a Site Should Load
website-speed-test-how-fast-should-a-site-load
A slow site loses people before they see what you offer. Here is how to measure speed for free and what to fix first on your own site.
---
How long do you wait for a website to open before you press back? Most people do not wait long, especially on a phone and on mobile data. A slow site loses customers before they have seen anything.

Speed matters to Google too. It measures how pages load for real visitors and uses it as one of the signals when ranking. A fast site will not put you in first place by itself, but a slow one can hold you back.

## How to measure speed

Open pagespeed.web.dev, paste the address of your site and press Analyze. You get a score from 0 to 100 for phone and for desktop. A score from 90 to 100 is excellent. A score from 50 to 89 means there is something to improve. Below 50 the site is probably losing visitors.

Look at the **phone** result first. It is stricter and closer to what your customers see.

## What the three main measures mean

Under the score, PageSpeed shows several measures with English abbreviations. The three most important ones are these.

**LCP**, or Largest Contentful Paint, is how long it takes for the biggest thing on the screen to appear, usually the main photo or the headline. It is good when it is under 2.5 seconds.

**INP**, or Interaction to Next Paint, is how quickly the page reacts when you press a button or a menu. If you tap and nothing happens for a moment, this is where the problem is.

**CLS**, or Cumulative Layout Shift, tells you whether the content jumps around while loading. You know the feeling. You want to tap a link, a banner loads at that very moment and you tap something else.

If the site has enough visitors, you will also see data from real users at the top. It matters more than the lab test, because it shows what happens to your real customers.

## The most common reasons for a slow site

Huge photos come first. A photo from a phone is four or five megabytes, and for a website it should be tens of times smaller, in the WebP format. Too many plugins come next, because every WordPress plugin loads its own files, so remove the ones you do not use. Weak hosting is another cause, since the cheapest hosting is often the slowest. Third party scripts such as chats, counters, embedded maps and videos load from other servers and slow the page down. A heavy theme or page builder can also load code for dozens of features of which you use three. And fonts count too, because five different fonts in several weights weigh more than you think.

## What to do today

Start with the photos, because that is the quickest win. Then remove the plugins you do not use. If the score stays low, the problem is probably the theme or the hosting, and it is time for a more serious conversation.

### Photos, step by step

First, reduce the photo to the size at which it will be shown before you upload it. For a full screen photo about 1600 to 2000 pixels wide is plenty, and for a photo in a card 800 pixels is enough. Second, convert it to **WebP**. Free tools such as squoosh.app do this in the browser without uploading anything anywhere. Third, if the site runs on WordPress, an optimisation plugin can do it automatically for every new photo.

### Embedded videos and maps

An embedded YouTube video or Google map loads a lot of code even if nobody presses it. A good solution is to show a picture with a button such as Play or Open the map, and load the real video or map only when it is pressed.

## Common questions

**Why is the result different every time I run the test?** The test runs from a Google server and depends on the load at that moment. A difference of a few points is normal. Watch whether the result is consistently green, yellow or red, and not the exact number.

**Do I need exactly 100?** No. A score above 90 is excellent. It is far more important that the site opens quickly for your real customers, on their phones and networks.

**Does a CDN or caching help?** Yes, especially on WordPress. Caching keeps ready made pages instead of building them again for every visit. It is usually set up at the hosting or with a plugin.

Our own site is built with a target of 100 in PageSpeed and you can check it yourself. If you want to know where yours stands, run the [free check](/en/website-check), which measures how fast your server responds. If a slow site is a constant problem, look at the [maintenance and hosting](/en/services/maintenance-and-hosting) service.
