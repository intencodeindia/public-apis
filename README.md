# Public APIs Landing Page

A simple and modern landing page built with [Bootstrap 5.3](https://getbootstrap.com/) that showcases popular free public APIs for developers. Use this project as a starting point to discover, share, or build applications using open APIs.

## 🚀 Features

- **Responsive Design:** Mobile-friendly and adapts to all devices.
- **Bootstrap 5.3:** Built using the latest Bootstrap version for rapid and easy customization.
- **API Highlights:** Curated list of popular public APIs.
- **Call to Action:** Invite users to contribute their favorite APIs.
- **Easy Customization:** Add, remove, or modify API cards in a single HTML file.

## 📸 Preview

<!-- Optionally add a screenshot here -->
<!-- ![Landing Page Preview](preview.png) -->

## 🛠️ Usage

1. **Clone or Download:**
   ```bash
   git clone https://github.com/intencodeindia/public-apis.git
   cd public-apis
   ```

2. **Open in Browser:**
   - Simply open `index.html` in your favorite web browser.

3. **Customization:**
   - Edit `index.html` to add or update API cards.
   - Change styles within the `<style>` tag or add your own CSS.

## 📝 Adding More APIs

To feature more APIs, copy and paste the card component inside the `<div class="row g-4">` in `index.html`, and update the details accordingly.

```html
<div class="col-md-4">
  <div class="card api-card h-100">
    <div class="card-body">
      <h5 class="card-title">API Name</h5>
      <p class="card-text">Short description about the API.</p>
      <a href="https://api-link.com" class="btn btn-primary" target="_blank">Explore API</a>
    </div>
  </div>
</div>
```

## 🌐 Live Demo

Deploy easily on GitHub Pages, Netlify, Vercel, or any static hosting.

## 📄 License

MIT License. See [LICENSE](LICENSE) for details.

## 🤝 Contributing

Contributions are welcome! Feel free to open issues, submit pull requests, or suggest APIs to feature.

---

> Inspired by the awesome [public-apis](https://github.com/public-apis/public-apis) project.
