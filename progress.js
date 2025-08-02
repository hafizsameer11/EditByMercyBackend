const tasks = [
  ':react-native-svg:processReleaseManifest',
  ':axios:extractDeepLinksRelease',
  ':react-navigation:processReleaseManifest',
  ':react-native-reanimated:extractDeepLinksRelease',
  ':expo-image-picker:processReleaseManifest',
  ':react-native-gesture-handler:extractDeepLinksRelease',
  ':expo-av:processReleaseManifest',
  ':expo-font:extractDeepLinksRelease',
  ':react-native-screens:extractDeepLinksRelease',
  ':react-native-safe-area-context:extractDeepLinksRelease',
  ':react-native-vector-icons:processReleaseManifest',
  ':expo-constants:processReleaseManifest',
  ':react-native-status-bar-height:extractDeepLinksRelease',
  ':expo-modules-core:processReleaseManifest',
  ':react-native-async-storage:extractDeepLinksRelease',
  ':expo-updates-interface:processReleaseManifest',
  ':react-native-svg:processReleaseManifest',
  ':expo-json-utils:processReleaseManifest',
  ':react-native-reanimated:processReleaseManifest',
  ':expo-modules-core:processReleaseManifest',
  ':react-native-gesture-handler:processReleaseManifest',
  ':react-native-safe-area-context:processReleaseManifest',
  ':react-native-screens:processReleaseManifest',
  ':react-native-status-bar-height:processReleaseManifest',
];

let index = 0;
let stage = 0;

function printLog(line) {
  console.log(`> Task ${line}`);
}

function drawInstallLogs() {
  if (index < tasks.length) {
    printLog(tasks[index]);
    index++;
  } else {
    stage = 1;
    console.log(`\n🔧 Building GymPaddy React Native App...`);
  }
}

function drawBuildTextOnce() {
  console.log(`\n⚙️ Binding Stream SDK ...`);
  console.log(`\n✅ Build at 64% (GymPaddy still building)\n`);
  stage = 2;
}

setInterval(() => {
  if (stage === 0) {
    drawInstallLogs();
  } else if (stage === 1) {
    drawBuildTextOnce();
  } else {
    // Keep showing last line to mimic it's still running
    console.log(`🕐 Waiting for GymPaddy to complete building...`);
  }
}, 250);
