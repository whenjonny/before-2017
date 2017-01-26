package org.intel.call;

import java.io.IOException;

import org.intel.com.Com;

/**
 * 如果 static变量 Ring为true则表明有电话接入
 * @author q
 *
 */
public class GetCall extends Thread {
	int c = 0;// 用来保存接收到的一个字符

	char c1 = ' ';
	char c2 = ' ';
	char c3 = ' ';
	public static boolean Ring = false;
	public Com cc;
	
	public GetCall(){
		cc = new Com("");
	}
	
	public static void main(String[] args){
		GetCall g = new GetCall();
		//g.cc.sendMessage("atd625543;\r");
		g.cc.sendMessage("at\r");
		g.start();
		while(true){
			if(GetCall.Ring)
				System.out.println("get a call");
		}
	}

	public void run() {
		try {
			while ((c = cc.inputStream.read()) != -1)// 如果接收到字符
			{
				System.out.print((char) c);// 则往屏幕打印
				if ((c3 == 'O') && ((char) c == 'K')) // 判断开头
				{
					// 表示成功街道指令
				}

				if(!Ring)//有电话:RING...
					if ((c1 == 'R') && (c2 == 'I') && (c3 == 'N')
						&& ((char) c == 'G'))// 如果检测到有电话接入
						Ring=true;
				c1 = c2;
				c2 = c3;
				c3 = (char) c;
			}
		} catch (IOException e) {
			e.printStackTrace();
		}
	}
}
