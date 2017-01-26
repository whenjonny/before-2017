package org.intel.jtts;

/**
 * Copyright 2008 - 2009
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 * 
 * @project loonframework
 * @author chenpeng
 * @email：ceponline@yahoo.com.cn
 * @version 0.1
 */
public class JTTSImpl implements JTTS {

	private static boolean init;

	/**
	 * 初始化指定路径下的TTS�?
	 * 
	 * @param path
	 */
	JTTSImpl(String path) {
		if (LIB.isWindows() && !init) {
			Espeak.loadLibrary("espeak_lib.dll");
			Espeak.loadLibrary("jtts.dll");
			init = true;
		} else {
			init = false;
			throw new RuntimeException(
					"Sorry,The current OS does not support this feature !");
		}
		Espeak.initialize(path);
		Espeak.setWordgap(2);
		Espeak.setCapitals(0);
		Espeak.setRate(150);
		Espeak.setVolume(100);
		Espeak.setPitch(50);
		Runtime.getRuntime().addShutdownHook(new Thread(new Runnable() {
			public void run() {
				if (!Espeak.isPlaying()) {
					Espeak.terminate();
				}
			}
		}));
	}

	/**
	 * 设定朗读速度
	 */
	public void setRate(int value) {
		Espeak.setRate(value);
	}

	/**
	 * 设定音调高低
	 */
	public void setPitch(int value) {
		Espeak.setPitch(value);
	}

	/**
	 * 设定发音对象
	 */
	public void setVoice(int gender, int age) {
		Espeak.setVoice(gender, age);
	}

	/**
	 * 设定大小写方�?
	 */
	public void setCapitals(int value) {
		Espeak.setCapitals(value);
	}

	/**
	 * 设定朗读间隔
	 */
	public void setWordgap(int value) {
		Espeak.setWordgap(value);
	}

	/**
	 * 设定音量
	 */
	public void setVolume(int value) {
		Espeak.setVolume(value);
	}

	/**
	 * 中止本次播放任务
	 */
	public final void terminate() {
		Espeak.terminate();
	}

	/**
	 * 朗读指定声音文件
	 */
	public synchronized final void speak(final String text) {
		Thread thread = new Thread() {
			public void run() {
				if (!Espeak.isPlaying()) {
					Espeak.synth(text);
				}
			}
		};
		thread.start();
	}

	/**
	 * 获取发音对象名称集合
	 */
	public String[] listVoiceNames() {
		return Espeak.listVoiceNames();
	}

	/**
	 * TTS语法规则
	 */
	public void setLanguage(String lanuage) {
		Espeak.setVoiceByName(lanuage);
	}

	/**
	 * 取消播放
	 */
	public final void cancel() {
		if (Espeak.isPlaying()) {
			Espeak.cancel();
		}
	}

	/**
	 * �?��是否在播放中
	 */
	public final boolean isPlaying() {
		return Espeak.isPlaying();
	}

	/**
	 * 当所有线程朗读完毕时转为此线程发�?
	 */
	public final void synchronize() {
		Espeak.synchronize();
	}

	/**
	 * 库文件版本信�?
	 */
	public final String getInfo() {
		return Espeak.info();
	}

}
