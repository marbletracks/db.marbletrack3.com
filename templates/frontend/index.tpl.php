<h1>Welcome to Marble Track 3!</h1>
<p class="hero-tagline">The ultimate gravity-powered theme park for marbles.</p>

<div class="hero-stats">
    <a href="/rides/">3 Rides</a> &middot;
    <a href="/workers/">Our Crew</a> &middot;
    <a href="/marbles/">Residents</a>
    <br>
    Open since 2017. Still expanding.
</div>

<div class="PagePanel hero-cta">
    <h2>Choose your size, pick your ride, and roll!</h2>
    <div class="ride-preview">
        <a href="/rides/the-grand-spiral/" class="ride-badge ride-large">The Grand Spiral<br><small>Large only</small></a>
        <a href="/rides/the-medium-descent/" class="ride-badge ride-medium">The Medium Descent<br><small>Medium only</small></a>
        <a href="/rides/the-triple-sneak-right/" class="ride-badge ride-small">The Triple Sneak-Right<br><small>Small only</small></a>
    </div>
</div>

<?php if (!empty($username)): ?>
    <div class="fix">
        <a href="/admin/">Admin</a> | <a href="/logout/">Logout</a>
    </div>
<?php endif; ?>