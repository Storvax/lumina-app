plugins {
    alias(libs.plugins.android.library)
    alias(libs.plugins.kotlin.android)
    alias(libs.plugins.kotlin.compose)
}

android {
    namespace = "pt.lumina.core.ui"
    compileSdk = 35

    defaultConfig {
        minSdk = 26
    }

    buildFeatures {
        compose = true
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

    implementation(platform(libs.compose.bom))
    implementation(libs.bundles.compose)
    implementation(libs.compose.material3)

    implementation(project(":core:core-common"))
}
