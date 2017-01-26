package org.intel.jmsc;

import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;

import org.tjumyk.MSC;

import sun.audio.AudioPlayer;
import sun.audio.AudioStream;

public class Test {

	/**
	 * @param args
	 * @throws IOException 
	 * @throws FileNotFoundException 
	 */
	public static void main(String[] args) throws FileNotFoundException, IOException {
		MSC instance = new MSC();//新建MSC对象
		instance.TTS("appid=4fee57a9", "ssm=1,auf=audio/L16;rate=16000,vcn=xiaoyan", "你好中国人", "test.wav");//调用TTS，几个参数分别是初始化设置�?会话参数，输入文本和输出音频的文件名（�?appid”后面要填上你自己用的id）�?
		AudioStream stream = new AudioStream(new FileInputStream("test.wav"));//通过AudioStream播放下载的音�?
		AudioPlayer.player.start(stream);
		String output = instance.ISR("appid=4fee57a9","sub=iat,ssm=1,auf=audio/L16;rate=16000,aue=speex,ent=sms16k,rst=plain", "test.wav");//调用ISR，几个参数分别是初始化设置�?会话参数、输入音频，返回值为识别出的文本（�?appid”后面要填上你自己用的id）�?
		System.out.println(output);
		//JOptionPane.showMessageDialog(null, output);//显示识别出来的文�?

	}
}
