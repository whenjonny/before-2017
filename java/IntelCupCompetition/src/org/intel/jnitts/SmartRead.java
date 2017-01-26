package org.intel.jnitts;

public class SmartRead {
	private final smartreadjava reader = new smartreadjava();
	private String string;
	private Thread thread;
	private int rate;
	private int volume;	
	
	public void setSpeed(int rate){ 
		this.rate = rate;
		reader.SmartRead_SetSpeed(rate);
	}
	
	public void setVolume(int volume){
		this.volume = volume;
		reader.SmartRead_SetVolume(volume);
	}
	
	public void play(String text) throws InterruptedException{
		string = text;
		thread = new Thread(new PlayAudio());
		thread.setDaemon(true);
		thread.start();
		//thread.join();
	}
	
	public void pause(){
		reader.SmartRead_PauseORContinue();
	}
	
	public void stop(){
		reader.SmartRead_Close();
	}
	
	public int getPosition(){
		return reader.SmartRead_GetLocationInfo();
	}
	
	private class PlayAudio implements Runnable {
		public void run() {
			reader.SmartRead_InitialAuth(0, 0, 0, 0, "support@smartysoft.com",
					"88888888", "111-111-111-111");
			reader.SmartRead_SetMode(0);
			reader.SmartRead_Speak(string, "   ", 1, 0, rate, volume, 0, -1,
					"");
		}
	}
	
	/**
	 * Useage
	 * @param args
	 * @throws InterruptedException
	 */
	public static void main(String[] args) throws InterruptedException{
		SmartRead t = new SmartRead();
		t.setSpeed(50);
		t.setVolume(99);
		t.play("你好");
		// 要等待足够的时间
		Thread.sleep(3000);
		while(t.getPosition()!=100)
			System.out.println(t.getPosition());
	}
}
