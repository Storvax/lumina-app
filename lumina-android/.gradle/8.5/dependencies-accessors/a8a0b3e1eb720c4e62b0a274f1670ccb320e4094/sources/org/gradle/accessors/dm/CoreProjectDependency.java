package org.gradle.accessors.dm;

import org.gradle.api.NonNullApi;
import org.gradle.api.artifacts.ProjectDependency;
import org.gradle.api.internal.artifacts.dependencies.ProjectDependencyInternal;
import org.gradle.api.internal.artifacts.DefaultProjectDependencyFactory;
import org.gradle.api.internal.artifacts.dsl.dependencies.ProjectFinder;
import org.gradle.api.internal.catalog.DelegatingProjectDependency;
import org.gradle.api.internal.catalog.TypeSafeProjectDependencyFactory;
import javax.inject.Inject;

@NonNullApi
public class CoreProjectDependency extends DelegatingProjectDependency {

    @Inject
    public CoreProjectDependency(TypeSafeProjectDependencyFactory factory, ProjectDependencyInternal delegate) {
        super(factory, delegate);
    }

    /**
     * Creates a project dependency on the project at path ":core:core-auth"
     */
    public Core_CoreAuthProjectDependency getCoreAuth() { return new Core_CoreAuthProjectDependency(getFactory(), create(":core:core-auth")); }

    /**
     * Creates a project dependency on the project at path ":core:core-common"
     */
    public Core_CoreCommonProjectDependency getCoreCommon() { return new Core_CoreCommonProjectDependency(getFactory(), create(":core:core-common")); }

    /**
     * Creates a project dependency on the project at path ":core:core-domain"
     */
    public Core_CoreDomainProjectDependency getCoreDomain() { return new Core_CoreDomainProjectDependency(getFactory(), create(":core:core-domain")); }

    /**
     * Creates a project dependency on the project at path ":core:core-network"
     */
    public Core_CoreNetworkProjectDependency getCoreNetwork() { return new Core_CoreNetworkProjectDependency(getFactory(), create(":core:core-network")); }

    /**
     * Creates a project dependency on the project at path ":core:core-ui"
     */
    public Core_CoreUiProjectDependency getCoreUi() { return new Core_CoreUiProjectDependency(getFactory(), create(":core:core-ui")); }

}
