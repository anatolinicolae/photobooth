import './App.css'

function App() {
  return (
    <div className="App">
      <header className="App-header">
        <h1>📸 Photobooth</h1>
        <p>Interactive Photo Capture System</p>
      </header>
      
      <div className="placeholder-content">
        <h2>
          <span className="status-indicator"></span>
          System Ready
        </h2>
        <p>This is a placeholder for the photobooth web application.</p>
        
        <h3>Planned Features:</h3>
        <ul>
          <li>Live camera preview</li>
          <li>Countdown display synchronized with LED semaphore</li>
          <li>Photo capture and gallery</li>
          <li>Social media sharing</li>
          <li>Filter and overlay options</li>
          <li>Print queue management</li>
        </ul>
        
        <p style={{ marginTop: '2rem', fontSize: '0.9rem', opacity: 0.8 }}>
          To get started, install dependencies and run the development server.
          See the README.md for instructions.
        </p>
      </div>
    </div>
  );
}

export default App
