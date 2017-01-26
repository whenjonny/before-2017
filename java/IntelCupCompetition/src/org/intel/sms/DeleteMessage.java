package org.intel.sms;

import java.util.LinkedList;
import java.util.List;

import org.smslib.CIncomingMessage;
import org.smslib.CService;
import org.smslib.ISmsMessageListener;


public class DeleteMessage {
	static CService srv;
	private static class CMessageListener implements ISmsMessageListener
	{
		public boolean received(CService service, CIncomingMessage message)
		{
			// Display the message received...
			System.out.println("*** Msg: " + message.getText());

			// and send a "thank you!" reply!
			try
			{
				srv.deleteMessage(message);
				// service.sendMessage(new
				// COutgoingMessage(message.getOriginator(), "Thank you!"));
			}
			catch (Exception e)
			{
				System.out.println("Could not send reply message!");
				e.printStackTrace();
			}

			// Return false to leave the message in memory - otherwise return
			// true to delete it.
			return false;
		}
	}

	public DeleteMessage()
	{
		srv = new CService("COM2", 57600, "Nokia", "");
	}

	public void doIt()
	{
		CMessageListener smsMessageListener = new CMessageListener();

		try
		{
			srv.setSimPin("0000");
			srv.setSmscNumber("");
			srv.connect();

			// Set the callback class.
			srv.setMessageHandler(smsMessageListener);
			// Set the polling interval in seconds.
			srv.setAsyncPollInterval(10);
			// Set the class of the messages to be read.
			srv.setAsyncRecvClass(CIncomingMessage.MessageClass.Read);

			// Switch to asynchronous POLL mode.
			srv.setReceiveMode(CService.ReceiveMode.AsyncPoll);
			// Or do you want to switch to CNMI mode???
			// srv.setReceiveMode(CService.ReceiveMode.AsyncCnmi);

			// Go to sleep - simulate the asynchronous concept...
			System.out.println();
			System.out.println("I will wait for a period of 60 secs for incoming messages...");
			try
			{
				Thread.sleep(60000);
			}
			catch (Exception e)
			{
			}
			System.out.println("Timeout period expired, exiting...");

			// Disconnect - Don't forget to disconnect!
			srv.disconnect();
		}
		catch (Exception e)
		{
			e.printStackTrace();
		}
	}
	
	public static void main(String[] args)
	{
		new DeleteMessage().doIt();
	}

}
