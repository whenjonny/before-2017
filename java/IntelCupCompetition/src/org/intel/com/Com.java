package org.intel.com;
import java.io.DataInputStream;
import java.io.DataOutputStream;
import java.io.IOException;

import javax.comm.CommDriver;
import javax.comm.CommPortIdentifier;
import javax.comm.SerialPort;

public class Com 
{
	static CommPortIdentifier portId;
	static SerialPort serialPort;
	public static DataOutputStream outputStream;
	public static DataInputStream inputStream;
	static CommDriver driver=null;
	private String defaultPort="COM2";
	
	public Com(String comPort)
	{
		if(!comPort.isEmpty()){
			this.defaultPort = comPort;
		}
	 	String driverName="com.sun.comm.Win32Driver";

		try {
			driver =(CommDriver) Class.forName(driverName).newInstance();
			portId=CommPortIdentifier.getPortIdentifier(defaultPort);

			// ���ô��ڵ����ֺͲ�֪��ʲô����
			serialPort=(SerialPort)portId.open("com",2000);
			outputStream = new DataOutputStream(serialPort.getOutputStream());//�������ʼ��
			inputStream = new DataInputStream(serialPort.getInputStream());// ��������ʼ��
		} catch (Exception e) {
			e.printStackTrace();
		}
		driver.initialize();
	}
	
	public void sendMessage(String message)
	{ 		 	
	 	// Find port and output
		try {
			serialPort.setSerialPortParams(4800,SerialPort.DATABITS_8,SerialPort.STOPBITS_1,SerialPort.PARITY_NONE);
			//serialPort.notifyOnOutputEmpty(true);
		} catch(Exception a){
			a.printStackTrace();
		}

		// Sending Message
		try
		{
			outputStream.write(message.getBytes());
			//System.out.println("send to port is sucess:  "+message+"\n");
		}
		catch(IOException a){}
	}
	
	public static void main(String[] args)
	{
		Com c = new Com("");//  ��������豸��������com�Ľӿ����趨��		
		c.sendMessage("1");
	}
}