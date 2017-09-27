// Canvas
var canvas = document.getElementById('gameCanvas');
var ctx;

// Game traits
var frameRate = 60;

// Player's paddle
var playerPaddleWidth = 150;
var playerPaddleHeight = 25;

// Image locations
var lavaImg = new Image();
lavaImg.src = "/brick-breaker/img/lava.webp";
var lavaCounter = 0;
var lavaSpriteTick = 5;



// Start game
window.onload = function() {
	ctx = canvas.getContext('2d');

function gameLoop () {

  window.requestAnimationFrame(gameLoop);
  
  coin.update();
  coin.render();
}
	setInterval(function() {
		update();
		drawFrame();
	}, 1000 / frameRate);
};

function drawFrame() {
	drawBackground();
	drawLava();
	drawPlayersPaddle();
}

function update() {

}


/**
 * Game component draw functions
 */

// Draw background
function drawBackground() {
	// Draw the background
	colorRect(0, 0, canvas.width, canvas.height, '#333');
}

// Draw the player's paddle
function drawPlayersPaddle() {
	colorRect((canvas.width/2)-(playerPaddleWidth/2), canvas.height - playerPaddleHeight, playerPaddleWidth, playerPaddleHeight, '#eee');
}

/**
 * Sprite draw funcitons
 */

// Draw the lava sprite
function drawLava() {
	for (var i = 0; i < canvas.width; i += 200) {
		ctx.drawImage(lavaImg, (lavaCounter%7) * 100 + 10, 115, 100, 20, i, canvas.height - 20, 200, 20);
	}
	console.log( (lavaCounter%3) * 180 + 10)
	lavaSpriteTick++;
	if (lavaSpriteTick%20 === 0) {
		lavaCounter++;
	}
}

/**
 * Simple draw functions
 */

// Rectangle
function colorRect(leftX, topY, width, height, drawColor) {
	ctx.fillStyle = drawColor;
	ctx.fillRect(leftX, topY, width, height);
}