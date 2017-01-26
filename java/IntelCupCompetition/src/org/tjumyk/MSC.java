package org.tjumyk;

import java.io.File;
import java.io.FileOutputStream;
import java.io.InputStream;

public class MSC {

	static {
		try {
			String[] dlls = { "msc", "MSCInvolke", "speex" };
			for (String dllName : dlls) {
				InputStream in = MSC.class.getClass().getResource("/" + dllName + ".dll").openStream();
				File dll = new File(System.getProperty("java.io.tmpdir") + dllName + ".dll");
				FileOutputStream out = new FileOutputStream(dll);

				int i;
				byte[] buf = new byte[1024];
				while ((i = in.read(buf)) != -1) {
					out.write(buf, 0, i);
				}

				in.close();
				out.close();
				dll.deleteOnExit();

				System.load(dll.toString());
			}
		} catch (Exception e) {
			System.err.println("load jni error!");
		}
	}

	/**
	 * Using TTS
	 * 
	 * @param configs
	 *            configuration for TTS
	 * @param params
	 *            parameters for TTS
	 * @param text
	 *            text to send
	 * @param fileName
	 *            name of sound file got from the server
	 * @return
	 */
	public native int TTS(String configs, String params, String text, String fileName);

	/**
	 * Using ISR
	 * 
	 * @param configs
	 *            configuration for ISR
	 * @param params
	 *            parameters for ISR
	 * @param inputFile
	 *            sound record to send
	 * @return result text
	 */
	public native String ISR(String configs, String params, String inputFile);

}
