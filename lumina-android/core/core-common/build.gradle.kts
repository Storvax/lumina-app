plugins {
    alias(libs.plugins.android.library)
    alias(libs.plugins.kotlin.android)
}

android {
    namespace = "pt.lumina.core.common"
    compileSdk = 35

    defaultConfig {
        minSdk = 26
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    kotlinOptions {
        jvmTarget = "17"
    }
    lint {
        targetSdk = 35
    }
    testOptions {
        targetSdk = 35
    }
}

dependencies {
    implementation(libs.kotlin.stdlib)
    implementation(libs.androidx.core)
}
