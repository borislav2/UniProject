Is Your Website Ready for AI Assistants in Five Steps
is-your-website-ready-for-ai-assistants
ChatGPT and similar tools now recommend shops and services. Here is what they need to see on your site in order to recommend you.
---
More and more people do not search for "restaurant Plovdiv" in Google. They ask ChatGPT where to have dinner tonight in Plovdiv. The assistant reads the websites on the person's behalf and offers a short list. If your site is not understandable to it, you are simply not on the list.

At eCommerce Annual Summit 2026, Metodi Drenovski of JetHost showed how fast this is happening. On their servers, **7.1 percent of all requests now come from AI bots**, more than from Googlebot. At the same time, people who reach a site through an AI assistant buy more often than average.

The logic is simple. When a person asks an assistant, they have already described in detail what they want. If the assistant sends them to you, the person arrives with a clear intent and not just to browse.

Here are the five steps he recommended, translated for a small business.

## 1. Access, so do not block the assistants

There are different AI bots. Some collect text to train models, others search in order to cite your site in an answer, and others open the page because a person has just asked. You can safely block the first kind, but **do not block the search ones**, for example OAI-SearchBot, ChatGPT-User and PerplexityBot.

A warning. From 15 September 2026 Cloudflare blocks AI bots by default for new sites and free plans. If you use Cloudflare, check the setting.

### How to check on your own

Open your domain followed by /robots.txt in the browser. This is a short text file with rules for bots. Look for a bot name followed by a line that says Disallow with a single slash. That line means the bot has no access to any page. If you see it under a search bot, the assistant cannot read your site. If you see the same under the line for all bots, marked with an asterisk, every bot is blocked, including Google.

### Which bots are for what

The bots for search and citing, which you should not block, are OAI-SearchBot and ChatGPT-User for ChatGPT, PerplexityBot and Perplexity-User for Perplexity, and Claude-SearchBot and Claude-User for Claude. The bots for training models are your choice, and they include GPTBot, ClaudeBot, Google-Extended and CCBot. If you block them, your content does not go into the training of future models, but assistants can still cite you when they search.

## 2. Clarity, which means data and not a banner

The assistant does not see your beautiful banner. It sees text. So the price, availability and delivery time should be in the text of the page and not only loaded by a script. Prices in euro, delivery with Econt or Speedy and cash on delivery should be described clearly. Descriptions should be your own in Bulgarian and not copied from the manufacturer. And the site should carry structured data, known as schema, for the address, opening hours and prices.

### A product page example

A page that is hard for an assistant has a photo of the product, a price inside a picture, a description saying "Super quality! Order now!" and a size table that loads only when you press a button. A page that is easy for an assistant has a title with the exact name of the product, the price as text, a line such as "In stock, delivery within two working days, cash on delivery", three or four sentences about what it suits, the sizes as text and short questions and answers underneath.

The second is better for people too. That is the general rule, because what helps the assistant usually helps the customer.

### A service example

If you run a hair salon, the assistant should be able to read on your site which city and district you are in, which services you offer, approximate prices, opening hours and how to book. If that information exists only in Instagram photos, the assistant does not see it.

## 3. Capacity, so the server must cope

Bots read many pages at once. Slow hosting that barely handles people will slow down even more. Caching and good hosting are no longer a luxury.

Signs that your hosting does not cope are a site that is slow at certain hours for no visible reason, sometimes returns a 500 or 503 error, or a hosting company writing to you about exceeded resources. If you see such things, a conversation about better hosting or caching is timely.

## 4. Protection

A large part of internet traffic comes from malicious bots. A shop put together in a hurry without thought for security is an open door. Updates, backups and protection against attacks are part of being visible, not something separate.

There needs to be a balance. Protection should stop attacks without stopping the useful bots. A firewall that is too aggressive may block both Google and the assistants without you knowing.

## 5. Measurement

Look in Google Analytics at how many visits come from chatgpt.com, perplexity.ai and gemini.google.com. Our own site already reports enquiries from these sources as a separate channel called AI assistants, so we know what works.

You can also ask the assistants directly. Ask the question the way your customer would, for example which online shop sells natural cosmetics with delivery to Varna, and see whether you are mentioned and where they take the information from.

## A short checklist

Check that robots.txt does not block the search bots of ChatGPT, Perplexity and Claude, and that the AI bot setting is reviewed if you use Cloudflare. Make sure the price, availability and delivery are in the text of the page and the descriptions are your own, in Bulgarian. Check that the site has structured data for the business and the products, that the hosting copes under load without errors, and that Analytics shows visits from AI assistants.

## Check it in a minute

The [free website check](/en/website-check) now also tests whether your robots.txt blocks AI assistants. If you want to be recommended, see [how we work on visibility in Google and AI](/en/services/seo-and-ai-visibility).

*Based on the talk "Is your shop ready for AI customers?" by Metodi Drenovski of JetHost at eCommerce Annual Summit 2026.*
