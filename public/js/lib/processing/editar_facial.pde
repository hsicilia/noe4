void setup()
{
  size(800,800);
  background(0, 0, 0);
  fill(255);
  PImage imagen = loadImage("");
  PFont fontA = loadFont("Verdana");
  textFont(fontA, 14);
}

void draw(){
	image(imagen, 0, 0);
  text("Hello Web!",20,20);
}