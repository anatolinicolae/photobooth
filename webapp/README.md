# Photobooth Web App

A React-based web application for the photobooth system.

## Features (Planned)

- Live camera preview
- Countdown display synchronized with LED semaphore
- Photo capture and gallery
- Social media sharing capabilities
- Photo filters and overlays
- Print queue management

## Installation

This project uses npm for dependency management.

### Install dependencies

```bash
cd webapp
npm install
```

## Development

### Start the development server

```bash
npm start
```

This will open [http://localhost:3000](http://localhost:3000) in your browser.

The page will reload when you make changes.

### Build for production

```bash
npm run build
```

Builds the app for production to the `build` folder.

## Available Scripts

- `npm start` - Runs the app in development mode
- `npm test` - Launches the test runner
- `npm run build` - Builds the app for production
- `npm run eject` - Ejects from Create React App (one-way operation)

## Project Structure

```
webapp/
├── public/          # Static files
│   └── index.html   # HTML template
├── src/             # Source files
│   ├── App.js       # Main app component
│   ├── App.css      # App styles
│   ├── index.js     # Entry point
│   └── index.css    # Global styles
├── package.json     # Dependencies and scripts
└── README.md        # This file
```

## Future Integration

This web app will eventually integrate with:
- The Python backend for camera control and image processing
- WebSocket or REST API for real-time status updates
- Arduino semaphore system for coordinated countdowns
