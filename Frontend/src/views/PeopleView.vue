   <template>
    <div>
      <h1>People</h1>
      <div v-if="people.length">
        <div v-for="person in people" :key="person.peopleName">
          <h2>{{ person.peopleName }}</h2>
          <ul>
            <li v-for="(neme, index) in person.names" :key="index">
              {{ name }}
            </li>
          </ul>
        </div>
      </div>
      <p v-else>Loading...</p>
    </div>
  </template>
  
  <script>
  import { BASE_URL } from "../helpers/baseUrls";
  import axios from "axios";
  
  export default {
    data() {
      return {
        urlApi: BASE_URL,
        people: [], 
      };
    },
    async mounted() {
      await this.peopleNames();
    },
    methods: {
      async peopleNames() {
        try {
          const url = `${this.urlApi}/people`; 
          const response = await axios.get(url);
          
          this.people = response.data?.data || [];
          
          console.log(this.people);
        } catch (error) {
          console.error("Error:", error);
        }
      },
    },
  };
  </script>
  