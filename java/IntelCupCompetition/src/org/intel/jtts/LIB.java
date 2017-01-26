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
public class LIB {

	final static public String encoding = "UTF-8";

	final static public String LS = System.getProperty("line.separator", "\n");

	final static public String FS = System.getProperty("file.separator", "\\");

	final static private boolean osIsLinux;

	final static private boolean osIsUnix;

	final static private boolean osIsMacOs;

	final static private boolean osIsWindows;

	final static private boolean osIsWindowsXP;

	final static private boolean osIsWindows2003;

	final static public String OS_NAME;

	final static public int JAVA_13 = 0;

	final static public int JAVA_14 = 1;

	final static public int JAVA_15 = 2;

	final static public int JAVA_16 = 3;

	final static public int JAVA_17 = 4;

	private static String javaVersion;

	final static private float majorJavaVersion = getMajorJavaVersion(System
			.getProperty("java.specification.version"));

	private static int tmpmajor = 0;

	final static private float DEFAULT_JAVA_VERSION = 1.4F;

	static {
		OS_NAME = System.getProperty("os.name").toLowerCase();
		osIsLinux = OS_NAME.indexOf("linux") != -1;
		osIsUnix = OS_NAME.indexOf("nix") != -1 || OS_NAME.indexOf("nux") != 1;
		osIsMacOs = OS_NAME.indexOf("mac") != -1;
		osIsWindows = OS_NAME.indexOf("windows") != -1;
		osIsWindowsXP = OS_NAME.startsWith("Windows")
				&& (OS_NAME.compareTo("5.1") >= 0);
		osIsWindows2003 = "windows 2003".equals(OS_NAME);
		javaVersion = System.getProperty("java.version");
		if (javaVersion.indexOf("1.4.") != -1) {
			tmpmajor = JAVA_14;
		} else if (javaVersion.indexOf("1.5.") != -1) {
			tmpmajor = JAVA_15;
		} else if (javaVersion.indexOf("1.6.") != -1) {
			tmpmajor = JAVA_16;
		} else if (javaVersion.indexOf("1.7.") != -1) {
			tmpmajor = JAVA_17;
		} else {
			tmpmajor = JAVA_13;
		}
	}

	final static private float getMajorJavaVersion(String javaVersion) {
		try {
			return Float.parseFloat(javaVersion.substring(0, 3));
		} catch (NumberFormatException e) {
			return DEFAULT_JAVA_VERSION;
		}
	}

	public static boolean isJDK13() {
		return majorJavaVersion == DEFAULT_JAVA_VERSION;
	}

	public static boolean isJDK14() {
		return majorJavaVersion == 1.4f;
	}

	public static boolean isJDK15() {
		return majorJavaVersion == 1.5f;
	}

	public static boolean isJDK16() {
		return majorJavaVersion == 1.6f;
	}

	public static boolean isJDK17() {
		return majorJavaVersion == 1.7f;
	}

	public static boolean isSun() {
		return System.getProperty("java.vm.vendor").indexOf("Sun") != -1;
	}

	public static boolean isApple() {
		return System.getProperty("java.vm.vendor").indexOf("Apple") != -1;
	}

	public static boolean isHPUX() {
		return System.getProperty("java.vm.vendor").indexOf(
				"Hewlett-Packard Company") != -1;
	}

	public static boolean isIBM() {
		return System.getProperty("java.vm.vendor").indexOf("IBM") != -1;
	}

	public static boolean isBlackdown() {
		return System.getProperty("java.vm.vendor").indexOf("Blackdown") != -1;
	}

	public static boolean isBEAWithUnsafeSupport() {
		if (System.getProperty("java.vm.vendor").indexOf("BEA") != -1) {
			String vmVersion = System.getProperty("java.vm.version");
			if (vmVersion.startsWith("R")) {
				return true;
			}
			String vmInfo = System.getProperty("java.vm.info");
			if (vmInfo != null) {

				return (vmInfo.startsWith("R25.1") || vmInfo
						.startsWith("R25.2"));
			}
		}

		return false;
	}

	public static String getJavaVersion() {
		return javaVersion;
	}

	public static int getMajorJavaVersion() {
		return tmpmajor;
	}

	public static boolean isLinux() {
		return osIsLinux;
	}

	public static boolean isMacOS() {
		return osIsMacOs;
	}

	public static boolean isUnix() {
		return osIsUnix;
	}

	public static boolean isWindows() {
		return osIsWindows;
	}

	public static boolean isWindowsXP() {
		return osIsWindowsXP;
	}

	public static boolean isWindows2003() {
		return osIsWindows2003;
	}

}
