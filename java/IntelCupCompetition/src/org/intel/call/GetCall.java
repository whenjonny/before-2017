package org.intel.call;

import java.io.IOException;

import org.intel.com.Com;

/**
 * ��� static���� RingΪtrue������е绰����
 * @author q
 *
 */
public class GetCall extends Thread {
	int c = 0;// ����������յ���һ���ַ�

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
			while ((c = cc.inputStream.read()) != -1)// ������յ��ַ�
			{
				System.out.print((char) c);// ������Ļ��ӡ
				if ((c3 == 'O') && ((char) c == 'K')) // �жϿ�ͷ
				{
					// ��ʾ�ɹ��ֵ�ָ��
				}

				if(!Ring)//�е绰:RING...
					if ((c1 == 'R') && (c2 == 'I') && (c3 == 'N')
						&& ((char) c == 'G'))// �����⵽�е绰����
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
