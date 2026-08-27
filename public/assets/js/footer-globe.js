/**
 * GST Footer Globe Animation
 * A single wireframe globe blooms, expands to fill the footer band, and fades,
 * carrying a rotating uppercase phrase in brand orange. The next globe begins
 * its bloom the instant the current one starts fading out (no dead gap).
 * Pure Canvas 2D, no external libraries. Fully disabled under prefers-reduced-motion.
 */
(function () {
  "use strict";

  var canvas = document.getElementById('footerGlobeCanvas');
  if (!canvas) return;

  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduceMotion) return;

  var ctx = canvas.getContext('2d');

  var PHRASES = [
    'SECURE BY DESIGN',
    'SCALABLE SECURITY',
    'ENGINEERED TO PROTECT',
    'CERTIFIED. TRUSTED. PROVEN.',
    'BUILT FOR MISSION SCALE'
  ];

  var GLOBAL_SPIN = 0.05;

  function makeGlobePoints(n) {
    var pts = [];
    var offset = 2 / n;
    var increment = Math.PI * (3 - Math.sqrt(5));
    for (var i = 0; i < n; i++) {
      var y = ((i * offset) - 1) + (offset / 2);
      var r = Math.sqrt(1 - y * y);
      var phi = i * increment;
      pts.push({ x: Math.cos(phi) * r, y: y, z: Math.sin(phi) * r });
    }
    return pts;
  }

  function easeOutCubic(t) { return 1 - Math.pow(1 - t, 3); }

  function drawTracked(ctx, text, cx, cy, spacing) {
    var chars = text.split('');
    var widths = chars.map(function (c) { return ctx.measureText(c).width; });
    var total = widths.reduce(function (a, b) { return a + b; }, 0) + spacing * (chars.length - 1);
    var x = cx - total / 2;
    for (var i = 0; i < chars.length; i++) {
      ctx.fillText(chars[i], x, cy);
      x += widths[i] + spacing;
    }
  }

  function Globe(cx, cy) {
    this.cx = cx; this.cy = cy;
    this.points = makeGlobePoints(20);
    this.rotY = Math.random() * Math.PI * 2;
    this.rotX = -0.22 + (Math.random() - 0.5) * 0.12;
    this.phase = 0;
    this.speed = 0.00042;
    this.state = 'in';
    this.opacity = 0;
    this.minR = 30;
    this.text = null;
  }

  Globe.prototype.maxRFor = function (canvasW, canvasH) {
    return Math.max(canvasW, canvasH) * 0.5;
  };

  Globe.prototype.update = function (dt, canvasW, canvasH) {
    this.rotY += GLOBAL_SPIN * dt * 0.06;
    var maxR = this.maxRFor(canvasW, canvasH);
    if (this.state === 'in') {
      this.phase += this.speed * dt;
      var e = Math.min(1, this.phase);
      this.opacity = Math.min(1, e * 3.2);
      this.scale = this.minR + (maxR - this.minR) * easeOutCubic(e);
      if (this.phase >= 1) { this.state = 'hold'; this.phase = 0; }
    } else if (this.state === 'hold') {
      this.phase += this.speed * dt * 0.9;
      this.opacity = 1;
      this.scale = maxR;
      if (this.phase >= 1) { this.state = 'out'; this.phase = 0; }
    } else if (this.state === 'out') {
      this.phase += this.speed * dt * 1.1;
      var e2 = Math.min(1, this.phase);
      this.opacity = Math.max(0, 1 - e2);
      this.scale = maxR * (1 + e2 * 0.15);
      if (this.phase >= 1) this.state = 'dead';
    }
  };

  Globe.prototype.textOpacity = function () {
    if (this.state === 'in') return Math.max(0, (this.phase - 0.55) / 0.45);
    if (this.state === 'hold') return 1;
    if (this.state === 'out') return Math.max(0, 1 - (this.phase / 0.55));
    return 0;
  };

  Globe.prototype.draw = function (ctx) {
    if (this.opacity <= 0.008) return;
    var r = this.scale;
    var cosX = Math.cos(this.rotX), sinX = Math.sin(this.rotX);
    var cosY = Math.cos(this.rotY), sinY = Math.sin(this.rotY);
    var proj = this.points.map(function (p) {
      var y1 = p.y * cosX - p.z * sinX, z1 = p.y * sinX + p.z * cosX;
      var x2 = p.x * cosY - z1 * sinY, z2 = p.x * sinY + z1 * cosY;
      var persp = 1.6 / (1.6 - z2 * 0.9);
      return { x: this.cx + x2 * r * persp, y: this.cy + y1 * r * persp, z: z2 };
    }, this);

    var glow = ctx.createRadialGradient(this.cx, this.cy, 0, this.cx, this.cy, r * 1.05);
    glow.addColorStop(0, 'rgba(242,134,29,' + (this.opacity * 0.16).toFixed(3) + ')');
    glow.addColorStop(1, 'rgba(242,134,29,0)');
    ctx.fillStyle = glow;
    ctx.beginPath(); ctx.arc(this.cx, this.cy, r * 1.05, 0, Math.PI * 2); ctx.fill();

    ctx.lineWidth = 1.4;
    for (var i = 0; i < proj.length; i++) {
      for (var j = i + 1; j < proj.length; j++) {
        var dx = proj[i].x - proj[j].x, dy = proj[i].y - proj[j].y;
        var d = Math.sqrt(dx * dx + dy * dy);
        if (d < r * 0.9) {
          var frontness = (proj[i].z + proj[j].z) / 2;
          var op = this.opacity * (0.35 + Math.max(0, frontness) * 0.5);
          ctx.strokeStyle = 'rgba(255,168,74,' + op.toFixed(3) + ')';
          ctx.beginPath(); ctx.moveTo(proj[i].x, proj[i].y); ctx.lineTo(proj[j].x, proj[j].y); ctx.stroke();
        }
      }
    }
    for (var k = 0; k < proj.length; k++) {
      var p = proj[k];
      var op2 = this.opacity * (0.55 + Math.max(0, p.z) * 0.45);
      var rad = (2 + Math.max(0, p.z) * 2.4) * Math.max(0.6, Math.min(1.4, r / 60));
      ctx.fillStyle = 'rgba(255,190,110,' + op2.toFixed(3) + ')';
      ctx.beginPath(); ctx.arc(p.x, p.y, rad, 0, Math.PI * 2); ctx.fill();
    }
  };

  var DPR = Math.min(window.devicePixelRatio || 1, 2);
  var W, H;
  function resize() {
    var rect = canvas.getBoundingClientRect();
    W = rect.width; H = rect.height;
    canvas.width = W * DPR; canvas.height = H * DPR;
    ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
  }
  resize();
  window.addEventListener('resize', resize);

  var textIdx = 0;
  function spawn() {
    var cx = W * 0.5, cy = H * 0.5;
    var g = new Globe(cx, cy);
    g.text = PHRASES[textIdx % PHRASES.length];
    textIdx++;
    return g;
  }

  var currentGlobe = spawn();
  var incomingGlobe = null;

  var last = performance.now();
  function frame(now) {
    var dt = Math.min(now - last, 48); last = now;
    ctx.clearRect(0, 0, W, H);

    if (currentGlobe.state === 'out' && !incomingGlobe) {
      incomingGlobe = spawn();
    }
    if (currentGlobe.state === 'dead' && incomingGlobe) {
      currentGlobe = incomingGlobe;
      incomingGlobe = null;
    }

    var globes = incomingGlobe ? [currentGlobe, incomingGlobe] : [currentGlobe];
    globes.forEach(function (g) {
      g.update(dt, W, H);
      g.draw(ctx);
      var textOp = g.textOpacity() * g.opacity;
      if (textOp > 0.01) {
        ctx.save();
        ctx.globalAlpha = textOp;
        ctx.fillStyle = '#F2861D';
        ctx.font = '600 15px Poppins, sans-serif';
        ctx.textAlign = 'left';
        ctx.textBaseline = 'middle';
        ctx.shadowColor = 'rgba(15,27,34,0.65)';
        ctx.shadowBlur = 8;
        drawTracked(ctx, g.text, g.cx, g.cy, 2.5);
        ctx.restore();
      }
    });

    requestAnimationFrame(frame);
  }
  requestAnimationFrame(frame);
})();
