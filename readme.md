# 📖 Twinkle_ReviewSentiment

<p align="center">
  <img src="https://img.shields.io/badge/Magento-2.4.7-blue.svg" alt="Magento 2.4.7 Supported" />
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License MIT" />
  <img src="https://img.shields.io/badge/Status-Stable-brightgreen.svg" alt="Stable" />
  <img src="https://img.shields.io/badge/Sentiment%20Analysis-AI%20Powered-ff69b4.svg" alt="Sentiment AI" />
</p>

---

Magento 2 module to **analyze product review sentiments** using **HuggingFace API** and **enhance review display** with interactive UI.

---

## ✨ Features

- Analyze new product reviews' sentiment automatically.
- Supports `POSITIVE`, `NEGATIVE`, `NEUTRAL` labels.
- Sentiment stored in a custom database column (`review_sentiment`).
- Frontend: Review sentiment badge shown with pulse animation and hover effects.
- Backend: Console command / Cron job to analyze existing reviews in bulk.
- Clean integration without affecting Magento core review functionality.
- Error-safe with logging (custom `review_sentiment.log`).

---

## 🔧 Installation

```bash
clone the repo using git clone
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

---

## ⚙️ Configuration

Admin Panel ➡️ Stores ➡️ Configuration ➡️ Review Sentiment

- **Enable Sentiment Analysis**: Yes / No
- **HuggingFace API Token**: Your API key
- **Model Endpoint**: API URL (optional override)

---

## 🛠 Technical Details

| Area | Description |
|:---|:---|
| Event Observer | `review_save_after` event is observed. |
| API Integration | HuggingFace Sentiment Model (default: `distilbert-base-uncased-finetuned-sst-2-english`). |
| Database | Adds `review_sentiment` column to `review` table. |
| Logging | Custom log file at `var/log/review_sentiment.log`. |
| Frontend | Animated badge (Pulse + Hover Zoom) for user engagement. |
| Command | CLI command to process old reviews: `bin/magento review:sentiment:analyze-existing` (optional). |

---

## 📈 Example Output

Review List:
```plaintext
[POSITIVE] Great product, loved it!
[NEGATIVE] Did not meet my expectations.
```

Frontend badge:

- ✅ POSITIVE (green, pulsing)
- ❌ NEGATIVE (red, pulsing)
- 😐 NEUTRAL (gray)

---

## 🖥 Console Commands

Analyze all existing reviews:
```bash
bin/magento review:sentiment:analyze-existing
```

(Processes reviews missing `review_sentiment`.)

---

## 🛡 Error Handling

- API timeouts or errors are caught and logged into `var/log/review_sentiment.log`.
- Review saving is **never blocked** by sentiment analysis failures.
- Safe fallback design.

---

## 📋 Future Enhancements

- Sentiment analytics dashboard (positive/negative pie charts 📊).
- Multi-language review sentiment support.
- Admin grid filtering by sentiment.

---

## 🤝 Contributing

We love contributions from the community! 🎉

Here’s how you can help:

1. **Fork** the repository.
2. **Create a feature branch**:
   ```bash
   git checkout -b feature/your-feature-name
   ```
3. **Make your changes**.
4. **Run tests** (if any).
5. **Commit** your changes:
   ```bash
   git commit -m "Added feature: your-feature-name"
   ```
6. **Push** to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```
7. **Submit a Pull Request** — Describe your feature / fix clearly.

✅ Follow Magento 2 coding standards.  
✅ Small, clean commits preferred.  
✅ Always explain *why* the change was needed.

---

