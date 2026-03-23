plugins {
    alias(libs.plugins.android.application)
    alias(libs.plugins.kotlin.android)
    alias(libs.plugins.kotlin.compose)
    alias(libs.plugins.kotlin.kapt)
    alias(libs.plugins.hilt)
}

android {
    namespace = "pt.lumina"
    compileSdk = 35

    defaultConfig {
        applicationId = "pt.lumina"
        minSdk = 26
        targetSdk = 35
        versionCode = 1
        versionName = "0.1.0"

        testInstrumentationRunner = "androidx.test.runner.AndroidJUnitRunner"
        vectorDrawables {
            useSupportLibrary = true
        }
    }

    buildTypes {
        release {
            isMinifyEnabled = false
            proguardFiles(
                getDefaultProguardFile("proguard-android-optimize.txt"),
                "proguard-rules.pro"
            )
        }
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    kotlinOptions {
        jvmTarget = "17"
    }

    buildFeatures {
        compose = true
    }

    packaging {
        resources {
            excludes += "/META-INF/{AL2.0,LGPL2.1}"
        }
    }
}

dependencies {
    // Core modules
    implementation(projects.core.coreUi)
    implementation(projects.core.coreNetwork)
    implementation(projects.core.coreAuth)
    implementation(projects.core.coreDomain)
    implementation(projects.core.coreCommon)

    // Feature modules
    implementation(projects.feature.featureAuth)

    // Kotlin
    implementation(libs.kotlin.stdlib)

    // AndroidX Core
    implementation(libs.androidx.core)
    implementation(libs.androidx.lifecycle.runtime)
    implementation(libs.androidx.lifecycle.viewmodel)
    implementation(libs.androidx.appcompat)
    implementation(libs.androidx.activity)

    // Compose
    implementation(platform(libs.compose.bom))
    implementation(libs.bundles.compose)
    implementation(libs.compose.ui.tooling.preview)
    debugImplementation(libs.compose.ui.tooling)
    debugImplementation(libs.compose.ui.test.manifest)

    // Compose Navigation
    implementation("androidx.navigation:navigation-compose:2.7.7")

    // Coroutines
    implementation(libs.bundles.coroutines)

    // Hilt (Dependency Injection)
    implementation(libs.hilt.android)
    kapt(libs.hilt.compiler)

    // Testing
    testImplementation(libs.junit)
    androidTestImplementation(libs.androidx.test.ext.junit)
    androidTestImplementation(libs.espresso.core)
    androidTestImplementation(platform(libs.compose.bom))
    androidTestImplementation(libs.compose.ui.test.junit4)
}

kapt {
    correctErrorTypes = true
}
