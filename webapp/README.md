# Photobooth Web App

A React + Vite web application for the photobooth system, built with Bun for blazing fast performance.

## Features (Planned)

- Live camera preview
- Countdown display synchronized with LED semaphore
- Photo capture and gallery
- Social media sharing capabilities
- Photo filters and overlays
- Print queue management

## Tech Stack

- **React 18** - UI library
- **Vite** - Lightning-fast build tool and dev server
- **Bun** - Fast JavaScript runtime and package manager

## Installation

This project uses Bun for package management. If you don't have Bun installed:

```bash
# macOS/Linux
curl -fsSL https://bun.sh/install | bash

# Or with Homebrew
brew install oven-sh/bun/bun
```

### Install dependencies

```bash
cd webapp
bun install
```

## Development

### Start the development server

```bash
bun run dev
```

This will start Vite dev server at [http://localhost:3000](http://localhost:3000) with:
- ⚡️ Lightning-fast HMR (Hot Module Replacement)
- 🔥 Instant server start
- 📦 Optimized dependency pre-bundling

### Build for production

```bash
bun run build
```

Builds the app for production to the `build` folder with:
- Minified and optimized code
- Tree-shaking for smaller bundle size
- Code splitting for better performance

### Preview production build

```bash
bun run preview
```

Locally preview the production build before deploying.

## Available Scripts

- `bun run dev` - Start development server with Vite
- `bun run build` - Build for production
- `bun run preview` - Preview production build locally

## Project Structure

```
webapp/
├── public/          # Static assets
├── src/             # Source files
│   ├── App.jsx      # Main app component
│   ├── App.css      # App styles
│   ├── main.jsx     # Entry point
│   └── index.css    # Global styles
├── index.html       # HTML template (Vite serves this)
├── vite.config.js   # Vite configuration
├── package.json     # Dependencies and scripts
└── README.md        # This file
```

## Why Vite + Bun?

### Vite Benefits:
- **Instant Server Start** - Uses native ES modules, no bundling needed in dev
- **Lightning Fast HMR** - Updates reflect instantly without full reload
- **Optimized Builds** - Rollup-based production builds with tree-shaking
- **Modern by Default** - Built for modern browsers, less legacy overhead

### Bun Benefits:
- **3x Faster Install** - Package installation is significantly faster than npm
- **Built-in Bundler** - All-in-one toolkit (runtime, bundler, package manager)
- **TypeScript Support** - Native TypeScript execution without compilation
- **Better Performance** - Written in Zig for maximum speed

## Future Integration

This web app will eventually integrate with:
- The Python backend for camera control and image processing
- WebSocket or REST API for real-time status updates
- Arduino semaphore system for coordinated countdowns

## Migration from Create React App

This project was migrated from Create React App to Vite for:
- ⚡️ 10-100x faster cold starts
- 🔥 Instant HMR vs slow refresh
- 📦 Much smaller bundle sizes
- 🛠️ Better developer experience
