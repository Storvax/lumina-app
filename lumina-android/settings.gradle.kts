pluginManagement {
    repositories {
        google()
        mavenCentral()
        gradlePluginPortal()
    }
}
plugins {
    id("org.gradle.toolchains.foojay-resolver-convention") version "1.0.0"
}

dependencyResolutionManagement {
    repositoriesMode.set(RepositoriesMode.FAIL_ON_PROJECT_REPOS)
    repositories {
        google()
        mavenCentral()
    }
}

rootProject.name = "Lumina"

include(
    ":app",
    ":core:core-ui",
    ":core:core-network",
    ":core:core-auth",
    ":core:core-domain",
    ":core:core-common",
    ":feature:feature-auth"
)

enableFeaturePreview("TYPESAFE_PROJECT_ACCESSORS")
