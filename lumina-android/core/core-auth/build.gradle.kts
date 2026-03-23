plugins {
    alias(libs.plugins.android.library)
    alias(libs.plugins.kotlin.android)
    alias(libs.plugins.kotlin.kapt)
    alias(libs.plugins.hilt)
}

android {
    namespace = "pt.lumina.core.auth"
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
    implementation(libs.androidx.security.crypto)
    implementation(libs.datastore)

    implementation(libs.hilt.android)
    kapt(libs.hilt.compiler)

    implementation(project(":core:core-common"))
}
