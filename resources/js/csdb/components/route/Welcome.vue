<template>
  <div class="welcome w-100 mt-10">
    <div class="flex justify-center">
      <div class="w-max">
        <h1 class="text-center text-9xl text-blue-500">Welcome</h1>
        <h5 class="italic text-left text-4xl text-yellow-500">Readme</h5>
        <span class="text-black italic text-sm inline text-right">V0.0.1</span>
      </div>
    </div>

    <div class="mt-10 lg:px-32 md:px-16 sm:px-8">
      <p>
        To whom interested making technical manuals using international standard S1000D, you are in the right place to do. This application is complied to the standard by the following feature:
        <ul>
          <li>Schema validation,</li>
          <li>CSDB object managing structure including: commenting, dispatcing, require managing.</li>
          <li>CSDB object document structure (limited to appliccrossreftable, brdoc, brex, comment, condcrossreftable, crew, ddn, descript, dml, frontmatter, pm, prdcrossreftable, schedule schema).</li>
          <li>Download xml object,</li>
          <li>Transform to PDF.</li>
        </ul>
      </p>

      <p>
        This application will always growth to satisfied every user and make it fully comply the international standard. The next update of this application include, but not limited to:
        <ul>
          <li>BREX Validator <span class="italic">(soon applied),</span></li>
          <li>CSDB status list publish,</li>
          <li>ICN metafile CSDB object.</li>
          <li>Support other schema</li>
        </ul>
      </p>

      <p>This page contains of four main menu: <router-link class="italic" v-for="(route, i) in $router.getRoutes()" :to="$router.resolve({name: route.name})">{{ route.name + (i+1 != ($router.getRoutes().length) ? ', ' : '.')}}</router-link>
        The <span class="italic">Explorer</span> menu is intended to create, read, update, or delete your CSDB Object. The doctype used are <span class="doctype">comment</span>, <span class="doctype">dmodule</span>, <span class="doctype">dml</span>, <span class="doctype">pm</span>, <span class="doctype">ICN</span> <span class="doctype">imf</span>.
      </p>
    </div>
  </div>
</template>