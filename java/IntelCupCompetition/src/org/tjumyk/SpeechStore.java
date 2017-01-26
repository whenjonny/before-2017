package org.tjumyk;



import java.io.BufferedOutputStream;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStream;

import javax.sound.sampled.AudioFormat;
import javax.sound.sampled.AudioInputStream;
import javax.sound.sampled.AudioSystem;
import javax.sound.sampled.DataLine;
import javax.sound.sampled.LineUnavailableException;
import javax.sound.sampled.TargetDataLine;

public class SpeechStore {
	
	private static byte[]  RIFF="RIFF".getBytes();
	private static byte[] RIFF_SIZE=new byte[4];
	private static byte[] RIFF_TYPE="WAVE".getBytes();
	
	
	private static byte[] FORMAT="fmt ".getBytes();
	private static byte[] FORMAT_SIZE=new byte[4];
	 private static byte[] FORMATTAG=new byte[2];
	 private static byte[] CHANNELS=new byte[2];
	private static byte[] SamplesPerSec =new byte[4];
	private static byte[] AvgBytesPerSec=new byte[4];
	private static byte[] BlockAlign =new byte[2];
	private static byte[] BitsPerSample =new byte[2];
	
	private static byte[] DataChunkID="data".getBytes();
	private static byte[] DataSize=new byte[4];
	public static boolean isrecording=false;
	
	
	
public void writeToWave(){
	 
}

public static void init(){
//这里主要就是设置参数，要注意revers函数在这里的作用
	 
	 FORMAT_SIZE=new byte[]{(byte)16,(byte)0,(byte)0,(byte)0};
	 byte[] tmp=revers(intToBytes(1));
	 FORMATTAG=new byte[]{tmp[0],tmp[1]};
	 CHANNELS=new byte[]{tmp[0],tmp[1]};
	 SamplesPerSec=revers(intToBytes(16000));
	 AvgBytesPerSec=revers(intToBytes(32000));
	  tmp=revers(intToBytes(2));
	 BlockAlign=new byte[]{tmp[0],tmp[1]};
	 tmp=revers(intToBytes(16));
	 BitsPerSample=new byte[]{tmp[0],tmp[1]};
}
public static byte[] revers(byte[] tmp){
	 byte[] reversed=new byte[tmp.length];
	 for(int i=0;i<tmp.length;i++){
		 reversed[i]=tmp[tmp.length-i-1];
		                 
	 }
	 return reversed;
}
public static byte[] intToBytes(int num){
	 byte[]  bytes=new byte[4];
	 bytes[0]=(byte)(num>>24);
	 bytes[1]=(byte)((num>>16)& 0x000000FF);
	 bytes[2]=(byte)((num>>8)& 0x000000FF);
	 bytes[3]=(byte)(num & 0x000000FF);
	 return bytes;
	 
}


public static void main(String[] args){
	 
	 
	InputStream input=capAudio();
	int toaldatasize=0;
	int audiolen;
	byte[] audiochunk=new byte[1024];
//因为文件�?��顺序读写，并且只能在�?��才能确定riffsize和datasize参数，所以对前面的data要缓存�?
	ByteArrayOutputStream bytebuff=new ByteArrayOutputStream(9600000);
	Timer tm=new Timer(20000);
	tm.start();
	try {
		while(isrecording){
			audiolen=input.read(audiochunk);
			toaldatasize+=audiolen;
			 bytebuff.write(audiochunk, 0, audiolen);
		}
	} catch (IOException e1) {
		// TODO Auto-generated catch block
		e1.printStackTrace();
	}
	
	 	DataSize=revers(intToBytes(toaldatasize));
	RIFF_SIZE=revers(intToBytes(toaldatasize+36-8));
	File wavfile= new File("F:\\writedformdata.wav");
	FileOutputStream file=null;
	
	 try {
		  file=new FileOutputStream(wavfile);
		  BufferedOutputStream fw=new BufferedOutputStream(file);
		  init();
    
	fw.write(RIFF);
	fw.write(RIFF_SIZE);
	fw.write(RIFF_TYPE);
	fw.write(FORMAT);
	fw.write(FORMAT_SIZE);
	fw.write(FORMATTAG);
	fw.write(CHANNELS);
	fw.write(SamplesPerSec);
	fw.write(AvgBytesPerSec);
	fw.write(BlockAlign);
	fw.write(BitsPerSample);
	
	fw.write(DataChunkID);
	fw.write(DataSize);
	fw.write(bytebuff.toByteArray());
	fw.flush();
	 } catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
}

//这是音频采集的部分�?
public static InputStream capAudio(){
	 float fFrameRate = 16000.0F;
		TargetDataLine target_line = null;
		AudioFormat format = new AudioFormat(AudioFormat.Encoding.PCM_SIGNED,
				fFrameRate, 16, 1, 2, fFrameRate, false);
		DataLine.Info lineInfo = new DataLine.Info(TargetDataLine.class,
				format, 65536);
		try {
			target_line = (TargetDataLine) AudioSystem.getLine(lineInfo);
			target_line.open(format, 655360);

		} catch (LineUnavailableException e) {
			System.err
					.println("ERROR: LineUnavailableException at AudioSender()");
			e.printStackTrace();
		}
		AudioInputStream audio_input = new AudioInputStream(target_line);
		target_line.start();
		isrecording=true;
		return audio_input;
}

public void stopRecord(){
	 
}
}


//Timer 是一个定时线程，到指定时间后将isrecording设置为false从�?停止采集音频�?

class Timer extends Thread{

	private int len;
	public Timer(int len_){
		this.len=len_;
	}
	public void run(){
		try {
			Thread.sleep(len);
		} catch (InterruptedException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		SpeechStore.isrecording=false;
	}
}

